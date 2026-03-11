function getInfo(){
    var url = new URL(location.href);
    var params = new URLSearchParams(url.search);
    searchID = params.get('ID');
    if(searchID == null || searchID == ""){
        alert('Invalid ID.');
        return false;
    }

    //get the search results from the PHP script in backend and exeute
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onload = processAPIResults;
    var searchURL = "../backend/APIFoodInfo.php?ID=" + searchID;
    xmlhttp.open("GET", searchURL, true);
    xmlhttp.send();
}

//This function runs when the XMLHTTP request gets a response
function processAPIResults(){
    const jsonResults = JSON.parse(this.responseText);

    //Prepares a great big ugly block of HTML to display onto the page

    var infoHTML = "";
    infoHTML += '<br><h2>Name: ' + jsonResults.food.food_name + '</h2>';
    infoHTML += "<h3>Type: " + jsonResults.food.food_type + "</h3>";

    infoHTML += "<p>ID: " + jsonResults.food.food_id + "</p><br>";

    infoHTML += "<h3>Serving: " + jsonResults.food.servings.serving[0].serving_description + "</h3>";
    infoHTML += "<h3>Calories: " + jsonResults.food.servings.serving[0].calories + "</h3>";

    infoHTML += "<br>";

    infoHTML += "<h3>Total Fat: " + jsonResults.food.servings.serving[0].fat + "g</h3>";
    infoHTML += "<p>Saturated Fat: " + jsonResults.food.servings.serving[0].saturated_fat + "g</p>";

    infoHTML += "<br>";
    
    infoHTML += "<h3>Cholesterol: " + jsonResults.food.servings.serving[0].cholesterol + "mg</h3>";
    infoHTML += "<h3>Sodium: " + jsonResults.food.servings.serving[0].sodium + "mg</h3>";
    infoHTML += "<h3>Protein:" + jsonResults.food.servings.serving[0].protein + "g</h3>";

    infoHTML += "<br>";

    infoHTML += "<h3>Total Carbohydrate: " + jsonResults.food.servings.serving[0].carbohydrate + "g</h3>";
    infoHTML += "<p>Fiber: " + jsonResults.food.servings.serving[0].fiber + "g</p>";
    infoHTML += "<p>Sugar: " + jsonResults.food.servings.serving[0].sugar + "g</p>";
    infoHTML += "<p>Added Sugar: " + jsonResults.food.servings.serving[0].added_sugars + "g</p>";

    infoHTML += "<br>";

    infoHTML += "<p>Vitamin A: " + jsonResults.food.servings.serving[0].vitamin_a + "mcg</p>";
    infoHTML += "<p>Vitamin C: " + jsonResults.food.servings.serving[0].vitamin_c + "mg</p>";
    infoHTML += "<p>Vitamin D: " + jsonResults.food.servings.serving[0].vitamin_d + "mg</p>";
    infoHTML += "<p>Potassium: " + jsonResults.food.servings.serving[0].potassium + "mg</p>";
    infoHTML += "<p>Calcium: " + jsonResults.food.servings.serving[0].calcium + "mg</p>";
    infoHTML += "<p>Iron: " + jsonResults.food.servings.serving[0].iron + "mg</p>";


    //keys with no value are not included in the json, showing as "undefined". It looks nicer to replace those with dashes.
    infoHTML = infoHTML.replaceAll("undefinedg", "-");
    infoHTML = infoHTML.replaceAll("undefinedmg", "-");
    infoHTML = infoHTML.replaceAll("undefinedmcg", "-");
    infoHTML = infoHTML.replaceAll("undefined", "-");

    //puts this mess into the "info" element
    document.getElementById('info').innerHTML = infoHTML;
}