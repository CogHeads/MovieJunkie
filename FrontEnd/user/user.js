$('.hnav ul li').click(function(){
    $(this).addClass("active").siblings().removeClass('active');
})

const tabBtn = document.querySelectorAll('.hnav ul li');
const tab = document.querySelectorAll('.tab');

function tabs(panelIndex) {
    tab.forEach(function(node){
        node.style.display = 'none';
    });
    tab[panelIndex].style.display = 'block';
}
document.getElementById("defaultOpen").click();