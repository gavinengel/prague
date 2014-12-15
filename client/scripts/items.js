
// ***************** Get All ********************************

function getItems(){

  var hr = new XMLHttpRequest();
  var url = 'items/';
  hr.open("GET", url, true);
  hr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  hr.onreadystatechange = function(){
    if (hr.readyState == 4 && hr.status == 200){
      
      var r = JSON.parse(hr.responseText);

      //create array to sort return values
      var keys = [], 
      i,
      len,
      k;

      for (k in r){
        if (r.hasOwnProperty(k)){
        keys.push(k);}
      }

      keys.sort();
      keys.reverse();
      len = keys.length;

      for (i = 0; i < len; i++){
        k = keys[i];
        makeInventory(r[k].item_name, r[k].url, keys[i], r[k].item_desc, r[k].quantity, r[k].price, r[k].ts, i);
        }
      
      document.getElementById("parsedinventory").innerHTML = inventory + "</div>";
      document.getElementById("jsoncell").innerHTML = "<pre>"+hr.responseText.substr(0, 50) +"...</pre>";
    }
  }    
  
  hr.send();
  document.getElementById("parsedinventory").innerHTML="processing data ...";
} 

//**************************** Add Item ******************************
function addItem(){
  var create = document.getElementById("create"),
  name = create.addname,
   description= create.adddescription, 
   price=create.addprice, 
   quantity = create.addquantity,
   status =  document.getElementById("statuscell"),
   vars, hr, url, itemlink;

  // required name and quantity
  if (name.value == ''){
    status.innerHTML="<span class='error'>Name must be completed.</span>";
    return;} 
  if (quantity.value == ''){
   status.innerHTML="<span class='error'>Quantity must be completed.</span>";
   return;}
  
  vars = "name="+name.value+"&description="+description.value+"&quantity="+quantity.value+"&price="+price.value;  
  
  //create request
  hr = new XMLHttpRequest();
  url = 'items/'
  hr.open("POST", url, true);

  hr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  hr.onreadystatechange = function(){
    if (hr.readyState == 4 && hr.status == 200){
      var r = JSON.parse(hr.responseText);
      for (o in r){
        itemlink = "<a href='http://"+r[o].url+"'>http://"+r[o].url+"</a>";         
      }     
    status.innerHTML='<span class=success>Successfully added. Resource: '+itemlink+'</span>';
    
    if (inventory != '') { inventory = "<div id=content name=content>";} 
    getItems();
    document.getElementById("jsoncell").innerHTML = hr.responseText.substr(0, 50);
    create.reset();        
    }
  }    
  hr.send(vars);
}



//****************************** Upate Item ******************************/

function updateItem(){
  var update = document.getElementById("update"),
  id = update.itemid,
  name = update.name,
  description = update.description,
  price = update.price,
  quantity = update.quantity,
  status =  document.getElementById("statuscell"),
  hr, url, vars

  //create the request
  hr = new XMLHttpRequest();
  url = 'items/'
 
 //validation
  if (name.value == ''){
    status.innerHTML="<span class='error'>Name must be completed.</span>";
    return;
  }
  if (quantity == ''){
    status.innerHTML="<span class='error'>Quantity must be completed.</span>";
    return;
  }
  
  vars = "itemid="+itemid.value+"&name="+name.value+"&description="+description.value+"&quantity="+quantity.value+"&price="+price.value;  
  
  hr.open("PUT", url, true);
  hr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  hr.onreadystatechange = function(){
    if (hr.readyState == 4 && hr.status == 200){
      status.innerHTML='<span class=success>Successfully Updated. ID:'+id.value+'</span>';
     
      if (inventory != '') { inventory = "<div id=content name=content>";} 
      getItems();
      document.getElementById("jsoncell").innerHTML = hr.responseText.substr(0, 50);
      update.reset();
      update.style.display="none";
    }
  }
  hr.send(vars);
  status.innerHTML="waiting ...";
} // end update


//*************************** Delete Item ***********************************/


function deleteItem(id){

  //create the request
  var hr = new XMLHttpRequest();
  var url = 'items/';
  var vars = "del_itemid="+id;  

  hr.open("DELETE", url, true);

  hr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  hr.onreadystatechange = function(){
    if (hr.readyState == 4 && hr.status == 200){
      var return_data = hr.responseText + '';

      document.getElementById("statuscell").innerHTML = "<span class='success'>Successfully Deleted item: " + id + "</span>";
      if (inventory != '') {inventory = "<div class='content float-left'>";}
      getItems();
      document.getElementById("jsoncell").innerHTML = return_data;
    }
  }    

  hr.send(vars);
}


//**************************  END AJAX **************************************


//populate the Update Form
function makeUpdate (id) {
  document.getElementById("update").style.display = "table-row";

   
  var hr = new XMLHttpRequest();
  var url = 'items/'+id;
  hr.open("GET", url, true);
  hr.onreadystatechange = function(){
    if (hr.readyState == 4 && hr.status == 200){
      var r = JSON.parse(hr.responseText);
       for (o in r){
        document.getElementById("itemid").value = r[o].item_id;
        document.getElementById("name").value = r[o].item_name;
        document.getElementById("description").value = r[o].item_desc;
        document.getElementById("price").value = r[o].price;
        document.getElementById("quantity").value = r[o].quantity;
        document.getElementById("statuscell").innerHTML= '';


    document.getElementById('jsoncell').innerHTML=hr.responseText.substr(0, 50);
      };
    }
  }    
  
  hr.send();

  }


  //make display items
  var inventory = "<div class='content float-left'>";
  function makeInventory(myname, myhref, id, item_desc, quantity, price, ts){
    inventory += "<div class='card'><div class='cardtitle'>"+myname+"</div>"+
    "<div class='cardbody'><div>Description: "+item_desc.substr(0, 75)+"</div><div>"+
    "Quantity: "+quantity+"</div><div>Price: $"+price+"<br>Resource Id: "+id+"</div><div><small><i>updated:"+ts+"</i></small></div></div>"+
    "<div class=cardfooter'><a href='#update'><input type='button' value='Update'class='button-blued' value='update' onClick='makeUpdate("+id+");'></a>"+
    "&nbsp;&nbsp;<input type='button' value='Delete' class='button-red' onclick='deleteItem("+id+");return false;'></div></div>";
 }

