document.addEventListener("DOMContentLoaded", function(){

const productoSelect = document.querySelector('select[name="producto"]');
const estiloWrapper = document.querySelector('.benditoai-camiseta-estilos');

if(!productoSelect || !estiloWrapper) return;

const toggleEstilo = () => {

if(productoSelect.value === 'camiseta'){
estiloWrapper.style.display = 'block';
}else{
estiloWrapper.style.display = 'none';
}

};

productoSelect.addEventListener('change', toggleEstilo);

toggleEstilo();

});