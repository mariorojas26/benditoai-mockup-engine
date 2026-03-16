document.addEventListener("submit", async function(e){

if(e.target.id !== "benditoai-trending-form") return;

e.preventDefault();

let formData = new FormData(e.target);
formData.append("action","benditoai_trending_generate");

let result = document.getElementById("benditoai-trending-result");

let loading = result.querySelector(".benditoai-loading");
let wrapper = result.querySelector(".benditoai-image-wrapper");
let img = result.querySelector(".benditoai-generated-image");
let download = result.querySelector(".benditoai-download-btn");

loading.style.display="block";
wrapper.style.display="none";

try{

let response = await fetch(benditoai_ajax.ajax_url,{
method:"POST",
body:formData
});

let data = await response.json();

if(data.success){

img.src=data.data.image_url;
download.href=data.data.image_url;

loading.style.display="none";
wrapper.style.display="block";

if(typeof benditoaiActualizarTokensInstantaneo === "function"){
benditoaiActualizarTokensInstantaneo();
}

}else{

loading.innerHTML="Error: "+data.data;

}

}catch(err){

loading.innerHTML="Error inesperado.";

}

});