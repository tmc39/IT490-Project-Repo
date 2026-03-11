function getInfo(){
    console.log("Sending request to API");
    document.getElementById('info').innerHTML = "please wait...";
    var url = new URL(location.href);
    var params = new URLSearchParams(url.search);
    var searchID = params.get('ID');
    if(searchID == null || searchID == ""){
        alert('Invalid ID.');
        return false;
    }

    //get the search results from the PHP script in backend and exeute
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onload = processAPIResults;
    var searchURL = "../backend/APIRecipeInfo.php?ID=" + searchID;
    xmlhttp.open("GET", searchURL, true);
    xmlhttp.send();
}

//This function runs when the XMLHTTP request gets a response
function processAPIResults(){ 
    //Cancel the process if the API gives no response
    console.log("processing result...");
    if(this.responseText == null || this.responseText == ""){
        document.getElementById('info').innerHTML = "There was a problem accessing the API.";
        return;
    }

    const jsonResults = JSON.parse(this.responseText);
    var infoHTML = "";

    //error catcher, in case the API returns an error
    if(jsonResults.error != null){
        infoHTML += "<p>ErrorCode " + jsonResults.error.code + ": </p>"
        infoHTML += "<p> " + jsonResults.error.message + "</p>"
        document.getElementById('info').innerHTML = infoHTML;
        return;
    }

    //Prepares a great big ugly block of HTML to display onto the page
    
    infoHTML += '<br></br><h2>Name: ' + jsonResults.recipe.recipe_name + '</h2>';
    infoHTML += "<p>ID: " + jsonResults.recipe.recipe_id + "</p>";

    if(jsonResults.recipe.recipe_images != null && jsonResults.recipe.recipe_images.recipe_image != null){
        infoHTML += '<img src="' + jsonResults.recipe.recipe_images.recipe_image[0] + '">'
    }

    if(jsonResults.recipe.recipe_description != null){
        infoHTML += '<p>' + jsonResults.recipe.recipe_description + '</p>';
    }

    infoHTML += "<br></br>";

    //print each ingredient in the recipe's ingedients, if it has any
    if(jsonResults.recipe.ingredients != null){
    infoHTML += "<h3>Ingredients</h3>";
    var i = 0;
    while(jsonResults.recipe.ingredients.ingredient[i] != null){
        infoHTML += '<p>';
        infoHTML += '<a target="_blank" href="foodinfo.php?ID=' + jsonResults.recipe.ingredients.ingredient[i].food_id + '">';
        infoHTML += jsonResults.recipe.ingredients.ingredient[i].food_name;
        infoHTML += '</a>: ' + jsonResults.recipe.ingredients.ingredient[i].ingredient_description + '</p>';

        //infoHTML += "<p>" + jsonResults.recipe.ingredients.ingredient[i].ingredient_description + "</p>"
        i++;
    }
    }

    infoHTML += "<br></br>";

    //print each step in the recipe's directions, if it has any
    if(jsonResults.recipe.directions != null){
    infoHTML += "<h3>Directions</h3>";
    var i = 0;
    while(jsonResults.recipe.directions.direction[i] != null){
        infoHTML += '<h4>Step ' + jsonResults.recipe.directions.direction[i].direction_number + ': </h4><p>' + jsonResults.recipe.directions.direction[i].direction_description + '</p>';
        i++;
    }
    }

    //keys with no value are not included in the json, showing as "undefined". It looks nicer to replace those with dashes.
    infoHTML = infoHTML.replaceAll("undefined", "-");

    //puts this mess into the "info" element
    console.log("API data has been processed!");
    document.getElementById('info').innerHTML = infoHTML;
}

function postReview(){
    document.getElementById('postReviewResult').innerText = "...";

    //gets the contents of the review
    const reviewText = document.getElementById('newReviewContent').value;

    if(reviewText == null || reviewText == ""){
        alert("Your review must contain body text.")
        return;
    }

    //gets whether or not the new review is positive
    var pos = false;
    if(document.getElementById('newReviewType').value = "positive"){
        pos = true;
    }
    const isPositive = pos;

    //gets the ID of the recipe being reviewed
    var url = new URL(location.href);
    var params = new URLSearchParams(url.search);
    var searchID = params.get('ID');
    if(searchID == null || searchID == ""){
        alert('Invalid ID.');
        return false;
    }
    const recipeID = searchID;

    postReviewSuccess(reviewText);
}

function postReviewSuccess(reviewText = ""){
    document.getElementById('postReviewResult').innerText = "Review submitted";
    document.getElementById('newReviewContent').value = "";
}