//API KEY
const API_KEY = config.API_KEY;

// OUTPUT FOR MOVIES.
let movieOutput = document.getElementById("movies");

const removeAllMovies = document.getElementById("removeAllMovies");

// DISPLAY WATCHLISTS ON PAGE LOAD.
window.onload=function displayMovieWatchlist(){
    // MOVIES
    let toWatch = JSON.parse(localStorage.getItem("movies")) || [];
    mtotal.innerHTML="Total Number of Movies: " + toWatch.length;
    if (toWatch.length >= 1) {
        movieOutput.innerHTML +=
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
        <table id="movieTable">
        <tr>
          <th>#</th>
          <th>Poster</th>
          <th>Name</th>
          <th>Rating</th>
          <th>Delete</th>
        </tr>
      </table>`;
    }
    var movietable = document.getElementById("movieTable");
    let mserial=1;
    for(let i = 0; i < toWatch.length; i++){
        axios.get("https://api.themoviedb.org/3/movie/"+toWatch[i]+'?api_key='+API_KEY+'&language=en-US')
        .then((response)=>{
            let movie = response.data;
            var mrow = movietable.insertRow(-1);
            var c1 = mrow.insertCell(0);
            var c2 = mrow.insertCell(1);
            var c3 = mrow.insertCell(2);
            var c4 = mrow.insertCell(3);
            var c5 = mrow.insertCell(4);
            c1.innerHTML = mserial;
            c2.innerHTML += `<img class="tableposter" src="http://image.tmdb.org/t/p/w400/${movie.poster_path}" onerror="this.onerror=null;this.src='../images/imageNotFound.png';"></img>`;  
            c3.innerHTML += `<a onclick="movieSelected('${movie.id}')" href="#">${movie.title}</a>`; 
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
            <i class="material-icons trash" style="cursor: pointer;font-size: 27px;" onclick="movieSplice('${movie.id}')">delete_forever</i>`;
            mserial++;
        })
        //Display "Clear List" button.
        removeAllMovies.style.display = "block";
    }
    if(toWatch.length == 0) {
        // SHOW A MESSAGE IF THERE ARE NO MOVIES IN THE LIST.
        movieOutput.innerHTML +=
        `<p class="infoText"> No movies under "Completed" . Add some. <a href="#" onclick="openRecommendMoviesBox()"> Here are some recommendations !</a> </p>`;
    }
}

// Recommend movies
const recommendedBox = document.querySelector(".recommendedBox");
function openRecommendMoviesBox(){
    document.getElementById("recommendedTitle").innerHTML = `Recommended Movies: <span class="reload"><i class="material-icons refresh" onclick="reloadRecommendedMovies()">autorenew</i></span>`;
    recommendedBox.classList.add("recommendedBoxActive");
    recommendMovies();
}
function recommendMovies(){
    // Get random year on each call, between 1990 - current year.
    let minYear = 1990;
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

    axios.get("https://api.themoviedb.org/3/discover/movie?api_key="+API_KEY+'&language=en-US&sort_by=popularity.desc&include_adult=false&include_video=false&page='+recommendedPage+'&primary_release_year='+recommendedYear)
        .then((response)=>{
            console.log(response)
            let movie = response.data.results;
            movie.length = 4;
            let output = "";
            for(let i = 0; i < movie.length; i++) {
                output +=
                `<div class="recommended_card" onclick="movieSelected(${movie[i].id})">
                <div class="recommendedOverlay">
                    <div class="recommendedInfo">
                        <h2>${movie[i].title}</h2>
                        <p>Rating: ${movie[i].vote_average} / 10 </p>
                        <p>Release date: ${movie[i].release_date}</p>
                    </div>
                </div>
                <div class="recommended_cardImg">
                    <img src="http://image.tmdb.org/t/p/w154/${movie[i].poster_path}" onerror="this.onerror=null;this.src='../images/imageNotFound.png';">
                </div>
                </div>`;
            }
            let recOutput = document.getElementById("recommendedOutput");
            recOutput.innerHTML = output;
        })
}

// Close recommended box.
document.getElementById("closeRecommended").addEventListener("click", ()=>{
    recommendedBox.classList.remove("recommendedBoxActive");    
})
//Delete movie from the movie watchlist.
  function movieSplice(id){
    let storedId = JSON.parse(localStorage.getItem("movies")) ||  [];
    let index = storedId.indexOf(id);
    storedId.splice(index, 1);
    localStorage.setItem("movies", JSON.stringify(storedId));

    //Notification that a movie was removed from watchlist.
    const removedWatchlist = document.getElementById("alreadyStored");
    removedWatchlist.innerHTML = "Removed from watchlist !";
    removedWatchlist.classList.add("alreadyStored");
    setTimeout(() => {
        added.classList.remove("alreadyStored");
        location.reload();
    }, 1500);
}



//Takes you to detailed movie info page.
function movieSelected(id){
    sessionStorage.setItem("movieId",id);
    window.open("../movie-page.html");
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

// Remove all movies.
removeAllMovies.addEventListener("click", ()=>{
    localStorage.removeItem("movies");

    //Notification that all movies are removed from watchlist.
    const removedAll = document.getElementById("alreadyStored");
    removedAll.innerHTML = "Removed all movies!";
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

// Reload recommended movies
function reloadRecommendedMovies(){
    recommendMovies();
}

