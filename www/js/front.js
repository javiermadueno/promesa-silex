window.onload = function(){

    $(document).on('submit', 'form', function(event){
        var e = event || window.event;
        e.preventDefault();
        enviaForm();
    });

	//var formulario = document.getElementsByTagName('form')[0];

	//formulario.onsubmit = compruebaForm;

    // set current tab
    var navitem = document.querySelector(".tabs li");
    //store which tab we are on
    var ident = navitem.id.split("_")[1];
    navitem.parentNode.setAttribute("data-current",ident);
    //set current tab with class of activetabheader
    navitem.setAttribute("class","active");

    //hide two tab contents we don't need
    var pages = document.querySelectorAll(".tabpage");
    for (var i = 0; i < pages.length; i++) {
      //pages[i].style.display="none";
      pages[i].classList.add('hidden');
    }

    //this adds click event to tabs
    var tabs = document.querySelectorAll(".tabs li");
    for (var i = 0; i < tabs.length; i++) {
      tabs[i].onclick=displayPage;
    }
};

function checkValidacionForm() {
    var forms = document.getElementsByTagName('form');
    for (var i = 0; i < forms.length; i++) {
        forms[i].noValidate = true;

        forms[i].addEventListener('submit', function(event) {
            //Prevent submission if checkValidity on the form returns false.
            if (!event.target.checkValidity()) {
                event.preventDefault();
                //Implement you own means of displaying error messages to the user here.
            }
        }, false);
    }
}

// on click of one of tabs
function displayPage() {
  var current = this.parentNode.getAttribute("data-current");
  console.log(this.id);
  var id = this.id.split("_")[1];

  if(id===current){
 	document.getElementById("tabpage_"+current).removeAttribute('style');
  	document.getElementById("tabpage_"+current).classList.toggle('hidden');
  	return;
  }
  //remove class of activetabheader and hide old contents
  document.getElementById("tab_" + current).removeAttribute("class");
  document.getElementById("tabpage_"+current).classList.add('hidden');

  //add class of activetabheader to new active tab and show contents
  this.setAttribute("class","active");
  document.getElementById("tabpage_" + id).classList.remove("hidden");
  this.parentNode.setAttribute("data-current",id);
};


function compruebaForm(event){
    var e = event || window.event;
    e.preventDefault();

    enviaForm();

	var nombre = document.getElementById('nombre');
	var email = document.getElementById('email');
	var mensaje = document.getElementById('mensaje');

    $(this).find('.error').each(function(){
        this.innerHTML = '';
    });

	var todoOk=1;

	if(nombre.value.length<=0){
		todoOk=0;
		document.getElementById('errorNombre').innerHTML="Debe introducir un nombre";
	}

	if(email.value.length <= 0 || !esEmail(email.value)){
		document.getElementById('errorEmail').innerHTML = "Debe introducir una direccion correcta";
		todoOk=0;
	}

	if(mensaje.value.length <=25){
		document.getElementById('errorMensaje').innerHTML = "Mensaje demasiado corto";
		todoOk=0;
	}

	if(todoOk){
		console.log("LLamada de envio");
		enviaForm();
		return false;
	}
	return false;

};

function esEmail(email){
	var regexp = /[\w-\.]{3,}@([\w-]{2,}\.)*([\w-]{2,}\.)[\w-]{2,4}/;
	if(regexp.test(email)){
		return true;
	}

	return false;
}

function enviaForm(){
	console.log("Se va a enviar el formulario");
    var form = document.getElementsByTagName('form')[0];
    var $form = $(form);
	var form_data = new FormData(form);
	$.ajax({
		url: $form.attr('action'),
		type: $form.attr('method'),
		data: form_data,
		cache: false,
		processData: false,
		contentType: false,
		beforeSend: function(){
			var sel = $('#form_submit');
			if(sel.length>0){
				sel.prop("disabled", true);
			}
		},
		success: function(html){
            var sel = $('#form_submit');
            if(sel.length>0){
                sel.prop("disabled", false);
            }
			var alerta = $(html.alerta).filter("#alerta")[0].outerHTML;
			$("#alerts").empty();
			$("#alerts").append(alerta);

            var form = $('form')[0];
			form.reset();
            $('#form_content').find('.help-inline').html('');

		},
        error: function(response) {

            var sel = $('#form_submit');
            if(sel.length>0){
                sel.prop("disabled", false);
            }

            var html = JSON.parse(response.responseText);
            $("#alerts").empty();
            $("#alerts").append(html.alerta);
            $('#form_content').html(html.form);


        }
	});
};


