//API KEY
const API_KEY = config.API_KEY;

// OUTPUT FOR TV SHOWS.
let  TvShowsOutput = document.getElementById("tvShows");

const removeAllTvShows = document.getElementById("removeAllTvShows");

//DISPLAY WATCHLISTS ON PAGE LOAD.
window.onload=function displayTVWatchlist(){
    // TV SHOWS
    let toWatchTvShows = JSON.parse(localStorage.getItem("series")) || [];
    stotal.innerHTML="Total Number of Shows: " + toWatchTvShows.length;
    if (toWatchTvShows.length >= 1) {
        TvShowsOutput.innerHTML +=
        `<style> 
        table {
            border-collapse: collapse;
            width: 900px;
            margin-left: 50px;
        } 
        td, th {
            border: 1px solid #cccccc;
            padding: 10px;
        }
        tr:nth-child(odd){background-color: #e6e6e6;}
        tr:nth-child(even){background-color: #f2f2f2;}
        th {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: center;
            background-color: #4CAF50;
            color: white;
        }
        .tableposter {
            height:100px;
        }
        </style>
        <table id="showTable">
        <tr>
          <th>#</th>
          <th>Poster</th>
          <th>Name</th>
          <th>Rating</th>
          <th>Delete</th>
        </tr>
      </table>`;
    }
    var tvtable = document.getElementById("showTable");
    let tvserial=1;
    for(let i = 0; i < toWatchTvShows.length; i++){
        axios.get("https://api.themoviedb.org/3/tv/"+toWatchTvShows[i]+'?api_key='+API_KEY+'&language=en-US')
        .then((response)=>{
            let series = response.data;
            var tvrow = tvtable.insertRow(-1);
            var c1 = tvrow.insertCell(0);
            var c2 = tvrow.insertCell(1);
            var c3 = tvrow.insertCell(2);
            var c4 = tvrow.insertCell(3);
            var c5 = tvrow.insertCell(4);
  			c1.innerHTML = tvserial;
            c2.innerHTML = `<img class="tableposter" src="http://image.tmdb.org/t/p/w400/${series.poster_path}" onerror="this.onerror=null;this.src='../images/imageNotFound.png';">`;
            c3.innerHTML += `<a onclick="showSelected('${series.id}')" href="#">${series.name}</a>`; 
            c4.innerHTML =  `<select name="rate" id="rate">
                                <option value="-">-</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                            </select>`;
            c5.innerHTML += `<style>
            .trash:hover{animation: trash 0.5s ease forwards infinite;color:gray;}
            @keyframes trash{0%{transform: rotate(0deg)}33%{transform:rotate(15deg)}66%{transform:rotate(-15deg)}100%{transform: rotate(0deg)}}
            </style>
            <i class="material-icons trash" style="cursor: pointer;font-size: 27px;" onclick="seriesSplice('${series.id}')">delete_forever</i>`;
            tvserial++;
        })
            //Display "Clear List" button.
            removeAllTvShows.style.display = "block";
    }
    if(toWatchTvShows == 0){
        // SHOW A MESSAGE IF THERE ARE NO MOVIES IN THE LIST.
        TvShowsOutput.innerHTML +=
        `<p class="infoText"> No shows under "Completed". Add some. <a href="#" onclick="openRecommendTvShowsBox()"> Here are some recommendations !</a> </p>`;
    }
}
// Recommend tv shows.
const recommendedBox = document.querySelector(".recommendedBox");
function openRecommendTvShowsBox(){
    document.getElementById("recommendedTitle").innerHTML = `Recommended TV Shows: <span class="reload"><i class="material-icons refresh" onclick="reloadRecommendedTvShows()">autorenew</i></span>`;
    recommendedBox.classList.add("recommendedBoxActive");    
    recommendTvShows();
}
function recommendTvShows(){
    // Get random year on each call, between 2000 - current year.
    let minYear = 2000;
    let maxYear = (new Date()).getFullYear();
    minYear = Math.ceil(minYear);
    maxYear = Math.floor(maxYear);
    let recommendedYear = Math.floor(Math.random() * (maxYear-minYear + 1)) + minYear;

    // Get random page on each call.
    let minPage = 1;
    let maxPage = 5;
    minPage = Math.ceil(minPage);
    maxPage = Math.floor(maxPage);
    let recommendedPage = Math.floor(Math.random() * (maxPage - minPage + 1)) + minPage;

    axios.get("https://api.themoviedb.org/3/discover/tv?api_key="+API_KEY+'&language=en-US&sort_by=popularity.desc&first_air_date_year='+recommendedYear+'&page='+recommendedPage+'&include_null_first_air_dates=false')
        .then((response)=>{
            console.log(response)
            let series = response.data.results;
            series.length = 4;
            let output = "";
            for(let i = 0; i < series.length; i++) {
                output +=
                `<div class="recommended_card" onclick="showSelected(${series[i].id})">
                <div class="recommendedOverlay">
                    <div class="recommendedInfo">
                        <h2>${series[i].name}</h2>
                        <p>Rating: ${series[i].vote_average} / 10 </p>
                        <p>Release date: ${series[i].first_air_date}</p>
                    </div>
                </div>
                <div class="recommended_cardImg">
                    <img src="http://image.tmdb.org/t/p/w154/${series[i].poster_path}" onerror="this.onerror=null;this.src='../images/imageNotFound.png';">
                </div>
                </div>`;
            }
            let recOutput = document.getElementById("recommendedOutput");
            recOutput.innerHTML = output;
        })
        .catch((err)=>{
            console.log(err);
        })
}
// Close recommended box.
document.getElementById("closeRecommended").addEventListener("click", ()=>{
    recommendedBox.classList.remove("recommendedBoxActive");    
})

// Delete a tv show from the watchlist.
function seriesSplice(id){
    let storedId = JSON.parse(localStorage.getItem("series")) || [];
    let index = storedId.indexOf(id);
    storedId.splice(index, 1);
    localStorage.setItem("series", JSON.stringify(storedId));
    
    //Notification that a tv shows was removed from watchlist.
    const removedWatchlist = document.getElementById("alreadyStored");
    removedWatchlist.innerHTML = "Removed from watchlist !";
    removedWatchlist.classList.add("alreadyStored");
    setTimeout(() => {
        added.classList.remove("alreadyStored");
        location.reload();
    }, 1500);
}

// Takes you to detailed tv show info page.
function showSelected(id){
    sessionStorage.setItem("showId", id);
    window.open("../shows-page.html");
    return false;
}

// SLIDER FOR RECOMMENED OUTPUT
let isDown = false;
let startX;
let scrollLeft;
const recommendedOutput = document.getElementById("recommendedOutput");
recommendedOutput.addEventListener("mousedown", (e)=>{
    isDown = true;
    startX = e.pageX - recommendedOutput.offsetLeft;
    scrollLeft = recommendedOutput.scrollLeft;
    e.preventDefault();
    console.log(startX);
})
recommendedOutput.addEventListener("mouseup", ()=>{
    isDown = false;
})
recommendedOutput.addEventListener("mouseenter", ()=>{
    recommendedOutput.classList.add("active");
})
recommendedOutput.addEventListener("mouseleave", (e)=>{
    recommendedOutput.classList.remove("active");
    isDown = false;
})
recommendedOutput.addEventListener("mousemove", (e)=>{
    if(!isDown) return;
    e.preventDefault();
    const x = e.pageX - recommendedOutput.offsetLeft;
    const walk = x - startX;
    recommendedOutput.scrollLeft = scrollLeft - walk;
})
// Remove all tv shows.
removeAllTvShows.addEventListener("click", ()=>{
    localStorage.removeItem("series");
    
    //Notification that all tv shows are removed from watchlist.
    const removedAll = document.getElementById("alreadyStored");
    removedAll.innerHTML = "Removed all tv shows!";
    removedAll.classList.add("alreadyStored");
    setTimeout(() => {
        added.classList.remove("alreadyStored");
        location.reload();
    }, 1500);
})

// Escape key closes down the recommended movies / tv shows box.
document.body.addEventListener("keydown", (e)=>{
    if ( e.code === "Escape") {
        recommendedBox.classList.remove("recommendedBoxActive");
    }
})

// Reload recommended TV Shows
function reloadRecommendedTvShows(){
    recommendTvShows();
}