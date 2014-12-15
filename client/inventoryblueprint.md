FORMAT: 1A
HOST: http://inventory-ktleary.rhcloud.com

# inventory
Inventory API is used to view and manage items in a collection 

# Items
Items related resources of the **Inventory API**

## Items Collection [/items]
### List all Items [GET]
+ Response 200 (application/json)

        {"311":{"item_id":"311","item_name":"coaster","quantity":"3","price":"1","item_desc":"picture of rhino","ts":"2014-08-15 09:55:45","url":"inventory-ktleary.rhcloud.com/items/311/"},{"312":{"item_id":"312","item_name":"chocolate bar","quantity":"10001","price":"333","item_desc":"dark 85%","ts":"2014-08-15 10:24:29","url":"inventory-ktleary.rhcloud.com/items/312/"},

### Create an Item [POST]
+ Request (application/x-www-form-urlencoded)

        "name=chair&description=middle century&quantity=1&price=525"

+ Response 201 (application/json)

        {"282":{"item_id":"282", "url": "inventory-ktleary.rhcloud.com/items/282/"}}

### Update an Item [PUT]

+ Request (application/x-www-form-urlencoded)

        "itemid=284&name=wallet&description=brown, folded&quantity=2&price=2"

+ Response 200 (application/json)
    
        "http:\/\/inventory-ktleary.rhcloud.com\/items\/284"

## Item [/items/{id}]
A single Item object with all its details

+ Parameters
    + id (required, number, `1`)

### Retrieve a Item [GET]
+ Response 200 (application/json)

    + Header

            X-My-Header: The Value

    + Body


            {"267":{"item_id":"267","item_name":"whale","item_desc":"small","price":"1","quantity":"9000"}}



### Remove an Item [DELETE]
+ Response 200

        "response : 1"

