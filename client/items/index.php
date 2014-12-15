<?php



// database connect -- need to substitute with environment variables
function connectDB(){
$dbhost = getenv("OPENSHIFT_MYSQL_DB_HOST");
$dbport = getenv("OPENSHIFT_MYSQL_DB_PORT");
$dbuser = getenv("OPENSHIFT_MYSQL_DB_USERNAME");
$dbpwd = getenv("OPENSHIFT_MYSQL_DB_PASSWORD");

  if ($_SERVER['HTTP_HOST'] == 'localhost') {
    $dsn="mysql:host=localhost;dbname=inventory;port=3306;";
    $dbuser="root";
    $dbpass="";
  }else{
      $dsn="mysql:host=127.4.142.130;port=3306;dbname=inventory";
      $dbuser=getenv("OPENSHIFT_MYSQL_DB_USERNAME");
      $dbpass=getenv("OPENSHIFT_MYSQL_DB_PASSWORD");
  }

  try {
    $dbh = new PDO(''.$dsn.'',''.$dbuser.'', ''.$dbpass.'', array( PDO::ATTR_PERSISTENT => true));
  } catch (PDOException $e) {
    echo "Error!: " . $e->getMessage() . "<br/>";
    die();
  }
  return($dbh);
} 


  // All Items in inventory -- specification only wants id, url (I gave name too, if want all, copy the getItemsByID code)
  function getAllItems()
    {
        $dbh = connectDB();
        $stmt = $dbh->prepare('
          select i.item_id, i.item_name, i.item_desc, 
          p.quantity, p.price, i.ts
          FROM items i
          LEFT JOIN item_properties p ON i.item_id = p.fk_item_id
        ');
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    //item by ID -- probably need to look closer at number/string typing
  function getItemByID($id){
    $dbh = connectDB();
    $stmt = $dbh->prepare('
      select i.item_id, i.item_name, i.item_desc,
      p.quantity, p.price
      FROM items i
      LEFT JOIN item_properties p ON i.item_id = p.fk_item_id
      WHERE i.item_id = :end; 
      ');
     
    $stmt -> bindParam(':end', $id);
    $stmt->execute();    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }


  // create inventory item -- returns last_id
  function createItem($name, $description){
    $dbh = connectDB();
   
    // prepare and insert item into items table (before item props)

    $stmt = $dbh->prepare('
        INSERT INTO items 
            (item_name, item_desc) 
        VALUES 
            (:item_name, :item_desc)
    ');
    $stmt->bindParam(':item_name', $name);
    $stmt->bindParam(':item_desc', $description);
    $stmt->execute();
    $last_id = $dbh->lastInsertId("item_id");

    return $last_id;
    }
      

  function createItemProperties($id, $description, $price, $quantity){

    $dbh = connectDB(); 
    $stmt = $dbh->prepare('
    INSERT INTO item_properties 
        (fk_item_id, description, price, quantity) 
    VALUES 
        (:id, :description, :price, :quantity)
    ');

    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':quantity', $quantity);
    
    return $stmt->execute();
  }





  function updateItem($item_id, $item_name, $item_desc){
    $dbh = connectDB();

    // prepare and insert item into items table (before item props)

    $stmt = $dbh->prepare('
        UPDATE items
        SET item_name = :item_name,
            item_desc = :item_desc
        WHERE item_id = :item_id
    ');
    $stmt->bindParam(':item_id', $item_id);
    $stmt->bindParam(':item_name', $item_name);
    $stmt->bindParam(':item_desc', $item_desc);
    $stmt->execute();
    $last_id = $dbh->lastInsertId("item_id");
    return $last_id;
  } 

  function updateItemProperties($fk_item_id, $description, $quantity, $price){
    $dbh = connectDB();
   
    // prepare and insert item into items table (before item props)
    
    $stmt = $dbh->prepare('
        UPDATE item_properties
        SET description = :description,
            quantity = :quantity,
            price = :price
        WHERE fk_item_id = :fk_item_id
    ');
    $stmt->bindParam(':fk_item_id', $fk_item_id);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':quantity', $quantity);   
    $result =   $stmt->execute();
    
  } 




  // get information about the server & request
  $actual_link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
  $actual_host = '';
  $method = $_SERVER['REQUEST_METHOD'];


// ************************************** REQUEST: GET *******************************


  if ($method=="GET"){
    
    $array = array(); 
    //get end of url, just the numbers
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $pathFragments = explode('/items/', $path);
    $end = end($pathFragments);
    $end= preg_replace('#[^0-9]#', '', $end);
    
    //Get ALl
    if ($end == '') {  
      $data = getAllItems();  

      //format response and add specified href
      foreach ($data as $row){
        $currentid = $row["item_id"];
        $currentname = $row["item_name"];
        $price = $row["price"];
        $quantity = $row["quantity"];
        $ts = $row["ts"];
        $item_desc = $row["item_desc"];
        //$currenturl = "$row->url";
        //$currentimage = "$row->image";
        $array[$currentid] = array('item_id'=>$currentid,'item_name'=>$currentname, 
          'quantity'=>$quantity, 'price'=>$price, 'item_desc'=>$item_desc, 'ts'=>$ts,
          'url'=> $_SERVER['HTTP_HOST']."/items/".$currentid."/");
      }
      //send all results in json
      header("Content-Type: application/json");
      echo  json_encode($array);
    } elseif (ctype_digit($end)){
      //Get the specified item and return specified properties
      $array = '';
      $data = getItemByID($end); 
      foreach ($data as $row){
        $currentid = $row["item_id"];
        $currentname = $row["item_name"];
        $item_desc = $row["item_desc"];
        $quantity = $row["quantity"];
        $price = $row["price"];
        $array[$currentid] = array('item_id'=>$currentid,'item_name'=>$currentname, 'item_desc'=> 
          $item_desc, 'price'=> $price, 'quantity'=> $quantity);
      }

        //send selected json
      header("Content-Type: application/json");
        echo json_encode($array);

      }

  }

  //end GET ITEM(S) ***************************************

  // ********************CREATE New (POST)  ******************** 


  // IF METHOD IS POST ADD ITEM TO DB
  if ($method == "POST") {


    //isset($var) ?: $var = "";

    //get values
    if (isset($_POST['name'])) {  
     $name=$_POST['name'];
    }

    if (isset($_POST['description']))  { 
      $description =  $_POST['description'];      
    }

    if (isset($_POST['quantity'])) {
      $quantity =  $_POST['quantity'];            

    }

    if (isset($_POST['price'])){
      $price =  $_POST['price'];  
    }
 
    $last_id = createItem($name, $description);
     //var_dump($last_id);
    
    //create initial item properties
    createItemProperties($last_id, $description, $price, $quantity);

      //header("Content-Type: application/json");
    echo '{"'.$last_id.'":{"item_id":"'.$last_id.'", "url": "' .$_SERVER['HTTP_HOST'].'/items/'.$last_id.'/"}}';
      
  }     // ************END CREATE (POST)  




  // UPDATE (PUT) ****************************************************************
  if ($method == "PUT"){
    $dbh = connectDB();

    parse_str(file_get_contents("php://input"), $put_vars);
    //  echo("data:". $put_vars["itemid"]);
    
    $item_id=$put_vars["itemid"];
    $item_name =$put_vars["name"];
    $description= $put_vars["description"];
    $quantity=$put_vars["quantity"];
    $price=$put_vars["price"];

   
    $last_id=updateItem($item_id,  $item_name,  $description);
    
    updateItemProperties($item_id,  $description,  $quantity,  $price);

    header("Content-Type: application/json");
    echo json_encode("http://".$_SERVER['HTTP_HOST']."/items/".$item_id);


}  // end PUT/UPDATE


  function deleteItem($item_id){
    $dbh = connectDB();
    $stmt = $dbh->prepare('
      DELETE from items where item_id=:item_id
    ');
    $stmt -> bindParam(':item_id', $item_id);
    return $stmt -> execute();
  }


/* ******************** DELETE *********************/
if ($method == "DELETE"){

  parse_str(file_get_contents("php://input"), $put_vars);
  //echo("data:". $put_vars["itemid"]);
  //echo("id " .$put_vars["del_itemid"]);

  $item_id = $put_vars["del_itemid"];

 // $sql = "DELETE from items where item_id=".$put_vars["del_itemid"]; //  set item_name='".$put_vars["name"]."', item_desc='".$put_vars["description"]."' where item_id=".$put_vars["itemid"];

  //$stmt = $dbh->prepare($sql);
  //$delresp = $stmt -> execute();
  $deletemsg = deleteItem($item_id); 
  echo json_encode("response : " . $deletemsg);
  
} // END DELETE


    ?>


