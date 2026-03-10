//sends an XMLHTTP response to APIFoodSearch.php in the backend, and then processes the results to display on the foodsearch.php webpage
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
    var searchURL = "../backend/APIFoodSearch.php?search=" + searchTerm + "&results=" + maxresults + "&page=" + pageNum;
    xmlhttp.open("GET", searchURL, true);
    xmlhttp.send();
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

    if(jsonResults.foods != null){
    if(jsonResults.foods.max_results > 1 && jsonResults.foods.total_results > 1){
        //The API has found multiple results

        //shows results counter to user and checks how many results are being shown before displaying them (due to varying format)
        document.getElementById('resultsCounter').innerHTML = "<b>Total Results:</b> " + jsonResults.foods.total_results + ", <b>Shown Results:</b> " + jsonResults.foods.max_results + ", <b>Page Number:</b> " + jsonResults.foods.page_number;

        //Create a header this script will create for the search results
        resultsList += "<table>";
        resultsList += "<tr>";
        resultsList += "<th>Name</th>";
        resultsList += "<th>Type</th>";
        resultsList += "<th>Description</th>";
        resultsList += "<th>ID</th>";
        resultsList += "</tr>";
        //Create the meat and bones of the search results table. The rows containing food item data
        var i = 0;
        while (jsonResults.foods.food[i] != null){
            resultsList += '<tr>';
            resultsList += '<td> <a target="_blank" href="foodinfo.php?ID=' + jsonResults.foods.food[i].food_id + '">' + jsonResults.foods.food[i].food_name + "</a></td>";
            resultsList += "<td>" + jsonResults.foods.food[i].food_type + "</td>";
            resultsList += "<td>" + jsonResults.foods.food[i].food_description + "</td>";
            resultsList += "<td>" + jsonResults.foods.food[i].food_id + "</td>";
            resultsList += "</tr></a>";
            i += 1;
        }
        resultsList += "</table>";
    }
    else if (jsonResults.foods.max_results == 1 || jsonResults.foods.total_results == 1){
        //The API formats differently if there is only one result!

        //shows results counter to user and checks how many results are being shown before displaying them (due to varying format)
        document.getElementById('resultsCounter').innerHTML = "<b>Total Results:</b> " + jsonResults.foods.total_results + ", <b>Shown Results:</b> " + jsonResults.foods.max_results;

        //Create a header this script will create for the search results
        resultsList += "<table>";
        resultsList += "<tr>";
        resultsList += "<th>Name</th>";
        resultsList += "<th>Type</th>";
        resultsList += "<th>Description</th>";
        resultsList += "<th>ID</th>";
        resultsList += "</tr>";
        //Create only one row for the only search result
        resultsList += "<tr>";
        resultsList += "<td>" + jsonResults.foods.food.food_name + "</td>";
        resultsList += "<td>" + jsonResults.foods.food.food_type + "</td>";
        resultsList += "<td>" + jsonResults.foods.food.food_description + "</td>";
        resultsList += "<td>" + jsonResults.foods.food.food_id + "</td>";
        resultsList += "</tr>";
        resultsList += "</table>";
    }
    else{
        //the API has found NO RESULTS
        resultsList = "<h3>Sorry, we found no food items by that name. Please try another term.</h3>";
    }
    }
    else{
        //fallback in case the json result is empty
        resultsList = "<h3>Sorry, we found no food items by that name. Please try another term.</h3>";
    }

    document.getElementById('resultsDiv').innerHTML = resultsList;

    //displays the raw json API response. I used this for debugging.
    //var resultsHTML = JSON.stringify(jsonResults);
    //document.getElementById('resultsDiv').innerHTML = resultsHTML;
}