document.addEventListener("DOMContentLoaded", function(){

const trendingForm = document.getElementById('benditoai-trending-form');
const wrapper = document.querySelector('.benditoai-wrapper-tendencia');
const tips = document.querySelector('.benditoai-tips');

if(!trendingForm) return;

trendingForm.addEventListener('submit', function(){
if(wrapper){
wrapper.classList.add('benditoai-generating');
}
if(tips){
tips.style.display='block';
}

});

});
