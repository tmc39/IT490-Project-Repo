//sends an XMLHTTP response to APIRecipeSearch.php in the backend, and then processes the results to display on the recipesearch.php webpage
function getSearchResults(){
    //replaces spaces with dashes in search term, as spaces causes shit to break
    var searchTerm = document.getElementById('searchterm').value.replace(/\s+/g, '-');
    var pageNum = document.getElementById('pagenum').value;
    if(searchTerm == null || searchTerm == ""){
        alert('Please fill in search field.');
        return false;
    }
    var maxresults = document.getElementById('searchnum').value;
    if(maxresults == null || maxresults > 10 || maxresults < 0){
        maxresults = 10;
    }

    //get the search results from the PHP script in backend and exeute
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onload = processAPIResults;
    var searchURL = "../backend/APIRecipeSearch.php?search=" + searchTerm + "&results=" + maxresults + "&page=" + pageNum;
    xmlhttp.open("GET", searchURL, true);
    xmlhttp.send();
}
function searchButton(){
    //using the main search button resets page number before sending search request
    document.getElementById('pagenum').value = Number(0);
    search();
}
function search(){
    getSearchResults();
    document.getElementById('prevpage').style.display = "block";
    document.getElementById('pagenum').style.display = "none";
    document.getElementById('nextpage').style.display = "block";
}

function nextPage(){
    document.getElementById('pagenum').value = parseInt(document.getElementById('pagenum').value) + 1;

    search();
}
function previousPage(){
    if(document.getElementById('pagenum').value > 0){
        document.getElementById('pagenum').value = parseInt(document.getElementById('pagenum').value) - 1;
        search();
    }
}

//This function runs when the XMLHTTP request gets a response
function processAPIResults(){
    const jsonResults = JSON.parse(this.responseText);
    var resultsList = "";

    if(jsonResults.recipes != null && jsonResults.recipes.recipe != null && jsonResults.recipes.total_results > 0){
    if(jsonResults.recipes.max_results > 1 && jsonResults.recipes.total_results > 1){
        //The API has found multiple results

        //shows results counter to user and checks how many results are being shown before displaying them (due to varying format)
        document.getElementById('resultsCounter').innerHTML = "<b>Total Results:</b> " + jsonResults.recipes.total_results + ", <b>Shown Results:</b> " + jsonResults.recipes.max_results + ", <b>Page Number:</b> " + jsonResults.recipes.page_number;

        //Create a header this script will create for the search results
        resultsList += "<table>";
        resultsList += "<tr>";
        resultsList += "<th>Name</th>";
        resultsList += "<th>Description</th>";
        resultsList += "<th>ID</th>";
        resultsList += "</tr>";
        //Create the meat and bones of the search results table. The rows containing food item data
        var i = 0;
        while (jsonResults.recipes.recipe[i] != null){
            resultsList += '<tr>';
            resultsList += '<td> <a target="_blank" href="recipeinfo.php?ID=' + jsonResults.recipes.recipe[i].recipe_id + '">' + jsonResults.recipes.recipe[i].recipe_name + "</a></td>";
            resultsList += "<td>" + jsonResults.recipes.recipe[i].recipe_description + "</td>";
            resultsList += "<td>" + jsonResults.recipes.recipe[i].recipe_id + "</td>";
            resultsList += "</tr>";
            i += 1;
        }
        resultsList += "</table>";
    }
    else if (jsonResults.recipes.max_results == 1 || jsonResults.recipes.total_results == 1){
        //The API formats differently if there is only one result!

        //shows results counter to user and checks how many results are being shown before displaying them (due to varying format)
        document.getElementById('resultsCounter').innerHTML = "<b>Total Results:</b> " + jsonResults.recipes.total_results + ", <b>Shown Results:</b> " + jsonResults.recipes.max_results;

        //Create a header this script will create for the search results
        resultsList += "<table>";
        resultsList += "<tr>";
        resultsList += "<th>Name</th>";
        resultsList += "<th>Description</th>";
        resultsList += "<th>ID</th>";
        resultsList += "</tr>";
        //Create only one row for the only search result
        resultsList += "<tr>";
        resultsList += '<td> <a target="_blank" href="recipeinfo.php?ID=' + jsonResults.recipes.recipe[0].recipe_id + '">' + jsonResults.recipes.recipe[0].recipe_name + "</a></td>";
        resultsList += "<td>" + jsonResults.recipes.recipe[0].recipe_description + "</td>";
        resultsList += "<td>" + jsonResults.recipes.recipe[0].recipe_id + "</td>";
        resultsList += "</tr>";
        resultsList += "</table>";
    }
    else{
        //the API has found NO RESULTS
        resultsList = "<h3>Sorry, we found no food recipes by that name. Please try another term.</h3>";
    }
    }
    else if(jsonResults.recipes != null){
        //fallback in case there are no results
        document.getElementById('resultsCounter').innerHTML = "<b>Total Results:</b> " + jsonResults.recipes.total_results + ", <b>Shown Results:</b> " + jsonResults.recipes.max_results;
        resultsList = "<h3>No results found.</h3>";
    }
    else{
        //fallback in case the json result is empty
        resultsList = "<h3>Error: server didn't return a json.</h3>";
    }

    document.getElementById('resultsDiv').innerHTML = resultsList;

    //displays the raw json API response. I used this for debugging.
    //var resultsHTML = JSON.stringify(jsonResults);
    //document.getElementById('resultsDiv').innerHTML = resultsHTML;
}