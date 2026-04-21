/*
----------runs at the start of the page. Tries to get information about the page's recipe from backend script.----------
*/
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

/*
----------This function runs when the XMLHTTP request to see recipe details gets a response----------
*/
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

/*
----------Runs when the user presses the submit review button. Runs ProcessPostReview() when its XMLHTTP request gets a response.----------
*/
function postReview(){
    //gets the contents of the review
    const reviewText = document.getElementById('newReviewContent').value;

    if(reviewText == null || reviewText == ""){
        alert("Your review must contain body text.")
        return;
    }

    document.getElementById('postReviewResult').innerText = "...";

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

    console.log("Gathered data from review form");

    //Attempt to submit a new review via the submitreview.php script
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onload = processPostReview;
    var reviewparameters = "text=" + reviewText.replaceAll(/\s+/g, "_") + "&isPositive=" + isPositive + "&recipe=" + recipeID;
    var searchURL = "lib/submitreview.php?" + reviewparameters;

    console.log("Preparing to send review form to submitreview");

    xmlhttp.open("GET", searchURL, true);
    xmlhttp.send();

    console.log("Sent review form to submitreview");
}

/*
----------Middle man between submitting a review and displaying whether or not the server accepted it. I'm too afraid to touch this out of fear of breaking it.----------
*/
function processPostReview(){
    postReviewResult(this.responseText);
}

/*
----------Displays results of an attempt to submit a review. Runs when the post_review request receives a response.----------
*/
function postReviewResult(resultStatus){
    document.getElementById('postReviewResult').innerText = resultStatus;
    document.getElementById('newReviewContent').value = "";

    //reloads review display, so the user may see their newly posted review
    getReviews();
}

/*
----------Runs when the page is trying to request from thhe server a list of reviews for the page's recipe. Runs displayReviews() when its XMLHTTP request is answered.----------
----------This also runs on page load.-----------
*/
function getReviews(){
    var url = new URL(location.href);
    var params = new URLSearchParams(url.search);
    var searchID = params.get('ID');
    if(searchID == null || searchID == ""){
        alert('Invalid ID.');
        return false;
    }
    const recipeID = searchID;

    console.log("prepared to request to see reviews");

    //Attempt to submit a new review via the loadreviews.php script
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onload = displayReviews;
    var reviewparameters = "recipe=" + recipeID;
    var searchURL = "lib/loadreviews.php?" + reviewparameters;

    console.log("Preparing to send review form to submitreview");

    xmlhttp.open("GET", searchURL, true);
    xmlhttp.send();

    console.log("Sent request to see reviews");

    return;
}

/*
----------Runs when this page gets a response to a list_reviews request. Prints each review onto the page.----------
*/
function displayReviews(){
    console.log("received review information from server");
    const jsonResults = JSON.parse(this.responseText);

    if(jsonResults.status != "success"){
        //returns whatever error was given
        document.getElementById('reviewslist').innerText = jsonResults.status + ": " + jsonResults.message;
        return;
    }
    else if(jsonResults.review == null){
        //this occurs if there are no reviews on the recipe
        document.getElementById('reviewslist').innerText = jsonResults.message;
        return;
    }

    //on success, the json message states the number of reviews the recipe has received
    var reviewResultsHTML = '<p>' + jsonResults.message + '</p><br></br>';

    //adds each received review into a fat chunk of HTML
    var i = 0;
    while(jsonResults.review[i] != null){
        //reviews are light green if they are positive, light pink if negative
        //also prints the username and text describing the review type
        if(jsonResults.review[i].isPositive == 1){
            reviewResultsHTML += '<div style="border-style: solid; background-color: LightGreen; margin: 20px 3px; padding: 3px;">';
            reviewResultsHTML += '<h2>' + jsonResults.review[i].username + '</h2>';
            reviewResultsHTML += '<h3>Recommends this recipe</h3>';
        }
        else if(jsonResults.review[i].isPositive == 0){
            reviewResultsHTML += '<div style="border-style: solid; background-color: LightPink; margin: 20px 3px; padding: 3px;">';
            reviewResultsHTML += '<h2>' + jsonResults.review[i].username + '</h2>';
            reviewResultsHTML += '<h3>Does not recommend this recipe</h3>';
        }
        else{
            //fallback to grey
            reviewResultsHTML += '<div style="border-style: solid; background-color: LightGrey; margin: 20px 3px; padding: 3px;">';
            reviewResultsHTML += '<h2>' + jsonResults.review[i].username + '</h2>';
        }
        reviewResultsHTML += '<p>' + jsonResults.review[i].reviewDescription.replaceAll("_", " ") + '</p>';

        reviewResultsHTML += '</div>';
        i++;
    }

    document.getElementById('reviewslist').innerHTML = reviewResultsHTML;
}