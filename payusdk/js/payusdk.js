window.addEventListener('DOMContentLoaded', function() {
  if(window.jQuery) {
            if (jQuery('#form-payusdk').is(":visible"))
            {
                eventFormPayuSDK();
            }


   if (jQuery('#preload-payusdk').is(":visible"))
   {
           if(!jQuery('.popup').is(':visible')) {
     jQuery('.container-popup').show(),
     jQuery('.popup').show();
        jQuery("#msj-error").html("");
     jQuery("#msj-error").hide();
          eventForm();
      }
   }
    } 
 }, true);

 function eventForm(r = ''){
        jQuery('select[name=pay-type]').on('change', function() {
        if (this.value === 'credit') {
            jQuery('#payusdk-cash').hide();
            jQuery('#payusdk-pse').hide();
            jQuery('#payusdk-credit').show();
            jQuery('input[name=idorder]').val(r.id_order);
            new Card({
            form: document.querySelector('#payusdkform'),
            container: '.card-wrapper'
          });

        }else if(this.value === 'cash'){
            jQuery('#payusdk-credit').hide();
            jQuery('#payusdk-pse').hide();
            jQuery('#payusdk-cash').show();
            jQuery("img.change-cash").click(function() {
                var type = jQuery(this).attr('data-type');
                jQuery('input[name=medium]').val(type);
                jQuery(this).removeClass( "gray-scale" );
                changeCash(type);
            });
            formCash();
            jQuery('input[name=idorder]').val(r.id_order);
        }else if(this.value === 'pse'){
            jQuery('#payusdk-credit').hide();
            jQuery('#payusdk-cash').hide();
            jQuery('#payusdk-pse').show();
            jQuery.ajax({
                    type : jQuery('#formPSEPayu').attr('method'),
                    url : jQuery('#formPSEPayu').attr('action'),
                    data : jQuery("#formPSEPayu").serialize() + "&psebanks=yes",
                    dataType: 'json',
                    beforeSend : function() {
                      jQuery('div#loader-payusdk').show();
                            },
                    success : function(r) {
                      jQuery('div#loader-payusdk').hide();
                      if (r.status) {
                        for(var key in r) {
                      jQuery('select#pse').append("<option value='"+r[key].pseCode+"'>"+r[key].description + "</option"); 
                      }  
                  }else{
                      jQuery("#msj-error").html('Error al consultar lista de bancos');
                      jQuery("#msj-error").show();
                  }

                            },
                    error: function(e, a, r){
                    console.log(e.responseText + a + r);
                            }
                    });
        }
            formPSE();
            jQuery('input[name=idorder]').val(r.id_order);
    });

  jQuery("#payusdkform").submit(function(e) {
                e.preventDefault();
    jQuery("#msj-error").html("");
    jQuery("#msj-error").hide();
   
                if (checkCard() == false){
     jQuery("#msj-error").html('El tipo de tarjeta no es aceptada');
     jQuery("#msj-error").show();
     return;
                }
                jQuery.ajax({
                    type : "POST",
                    url : jQuery('#payusdkform').attr('action'),
                    data : jQuery("#payusdkform").serialize(),
                    beforeSend : function() {
                     jQuery('#loader-payusdk').show();
                     jQuery('input[type="submit"].form-payu').prop('disabled', true);
                    },
                    success : function(response) {
                     jQuery('#loader-payusdk').hide();
                     jQuery('input[type="submit"].form-payu').prop('disabled', false);
      
                       console.log(response);
                     var response = JSON.parse(response);
      
      if(response.status){
       jQuery('div.form-container-payusdk').hide();
       jQuery('div#alertpayusdk').show();

       if (response.estado === 'Aprobado'){
           jQuery('div#alertpayusdk').addClass('alert-success');
       }else{
           jQuery('div#alertpayusdk').addClass('alert-info');
       }
       jQuery('div#alertpayusdk strong').text(response.estado);
       jQuery('div#alertpayusdk p').text(response.message + ' ' + 'transactionId: ' + response.transactionId);
      }else{
       jQuery("div#msj-error").html(response.message);
       jQuery("div#msj-error").show();
       
       
      }
                      
                    },
                    error: function(e, a, r){
      jQuery('#loader-payusdk').hide();
                      jQuery('input[type="submit"].form-payu').prop('disabled', false);
                     console.log(e.responseText + a + r);
                    }
                });
            });
            jQuery("button#restoreOderPayusdk").click(function(){
                    var result = window.confirm('Seguro desea cancelar');
                    if (result) {
                      window.location.replace(jQuery('input[name=restoreOrder]').val() + '?idorder=' + jQuery('input[name=idorder]').val());
                    }
            }); 
 }

    function eventFormPayuSDK(){
        jQuery('#form-payusdk').submit(function(e){
        e.preventDefault();
        jQuery.ajax({
                    type : "POST",
                    url : jQuery(this).attr('action'),
                    beforeSend : function() {
                    jQuery('#module-payusdk-payment').css('cursor','progress');
                    jQuery('div#preload-payusdk').show();
                            },
                    success : function(r) {
                      jQuery('#module-payusdk-payment').css('cursor','default');
                      jQuery('div#preload-payusdk').hide();
                    var r = JSON.parse(r);
                    //if false  redirect step 1
                    if (r.status) {
                        jQuery('#validation-payusdk').hide();
                        jQuery('.form-container-payusdk').show();
                        eventForm(r);

                    }       
                              
                            },
                    error: function(e, a, r){
                    console.log(e.responseText + a + r);
                            }
                    });
        });
    }

    function checkCard(){
    var classCard = jQuery(".jp-card-identified" ).attr( "class" );
    switch(true) {
    case (classCard.indexOf('visa') !== -1):
        jQuery("input[name=cc_type]").val('VISA');
        return true;
        break;
    case (classCard.indexOf('mastercard') !== -1):
        jQuery("input[name=cc_type]").val('MASTERCARD');
        return true;
        break;
    case (classCard.indexOf('amex') !== -1):
        jQuery("input[name=cc_type]").val('AMEX');
        return true;
        break;
    case (classCard.indexOf('diners') !== -1):
        jQuery("input[name=cc_type]").val('DINERS');
        return true;
        break;    
    default:
        return false;
    }
  }

  function formCash(){
    jQuery('#formCashPayu').submit(function(e){
        e.preventDefault();
        var medium = jQuery('input[name=medium]').val();
        if (medium == '') {
            jQuery("#msj-error").html('Debe seleccionar un medio de pago efecty o baloto');
            jQuery("#msj-error").show();
            return;
        }
        jQuery.ajax({
            type : "POST",
            url : jQuery(this).attr('action'),
            data : jQuery(this).serialize(),
            beforeSend : function() {
                        jQuery('div#loader-payusdk').show();
                        jQuery('input[type="submit"].form-payu').prop('disabled', true);
                    },
            success : function(r) {
                        jQuery('div#loader-payusdk').hide();
                        jQuery('input[type="submit"].form-payu').prop('disabled', false);
                        
                        console.log(r);
                        var r = JSON.parse(r);
                        
                        if(r.status){
                            jQuery('#payusdk-cash').html("<iframe src='"+r.urlhtml+"' id='iframe-payusdk'></iframe>");
                        }else{
                            jQuery("#msj-error").html(r.message);
                            jQuery("#msj-error").show();
                            
                            
                        }
                      
                    },
                    error: function(e, a, r){
                     jQuery('#loader-payusdk').hide();
                     jQuery('input[type="submit"].form-payu').prop('disabled', false);
                        console.log(e.responseText + a + r);
                    }

        });
    })
  }

  function formPSE(){
    jQuery('#formPSEPayu').submit(function(e){
      e.preventDefault();
      jQuery.ajax({
            type : "POST",
            url : jQuery(this).attr('action'),
            data : jQuery(this).serialize(),
            beforeSend : function() {
                        jQuery('div#loader-payusdk').show();
                        jQuery('input[type="submit"].form-payu').prop('disabled', true);
                    },
            success : function(r) {
                        jQuery('div#loader-payusdk').hide();
                        jQuery('input[type="submit"].form-payu').prop('disabled', false);
                
                        console.log(r);
                        var r = JSON.parse(r);
                        
                        if(r.status){
                          jQuery('div#loader-payusdk').html("<h1 style='color:#000;text-align:center;'>Redireccionando ...</h1>");
                          jQuery('div#loader-payusdk').show();
                          window.location.replace(r.urlbank);
                        }else{
                            jQuery("#msj-error").html(r.message);
                            jQuery("#msj-error").show();
                            
                            
                        }
                      
                    },
                    error: function(e, a, r){
                     jQuery('#loader-payusdk').hide();
                     jQuery('input[type="submit"].form-payu').prop('disabled', false);
                     jQuery("#msj-error").html(e.responseText + a + r);
                     jQuery("#msj-error").show();
                    }

        });
    });
  }

  function changeCash(type){
      jQuery("img.change-cash").not("[data-type="+type+"]").addClass( "gray-scale" );
  }