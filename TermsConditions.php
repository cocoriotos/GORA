<!DOCTYPE html>
<?php
/*include "sessions.php";
include "sessionvalidation.php";*/
?>
<html lang="es">
<head>
<meta charset="UTF-8">
<link rel="icon" href="GORA.ico" type="image/x-icon">
<link rel="stylesheet" href="style_sheet.css"/>
</head>
<header>
  <nav class="navbar navbar-dark bg-dark d-flex justify-content-center" id="welcome">
    <center><a id="welcome" class="navbar-brand" data-i18n="pageTitle">TÉRMINOS Y CONDICIONES</a></center>
  </nav>
  <nav class="navbar navbar-dark bg-dark d-flex justify-content-center" id="welcome">
    <center><a id="welcome" class="navbar-brand" data-i18n="pageSubtitle">Consideraciones y cubrimientos de Servicios</a></center>

  </nav>
  <?php include __DIR__ . "/social_links.php"; ?>
	</header>
  
  
  	
<body id="bodyadminmodule">
          <br><br>
     
  <div class="container1">
      <class="column-wrap clearfix">
            <div class="col-xs-12">
                <hr>
            </div>
      <center><div class="col-xs-12 col-sm-12 column-custom-wrap">
                <div class="column-custom">
                    <h3 align="center" style="padding-bottom: 5px" data-i18n="termsTitle">Beneficios y Condiciones de Uso de la Aplicación</h3>
                    <!--<p> Video paso a paso del uso de la aplicación. Click Aquí</p><br>-->
                    <br>
                    <center><p style="color: blue; font-weight: bold; font-size: 24px;" data-i18n="benefitsHeading">Ventajas</p></center>
                    <p data-i18n="benefitsIntro">Gracias por usar la herramienta o la versión gratuita de 30 días calendario de la biblioteca de Contenidos Útiles, que te ayuda a:</p><br>
                    <p data-i18n="benefit1">1. Etiquetar el contenido con una breve descripción para que puedas encontrarlo fácilmente.</p>
                    <p data-i18n="benefit2">2. Tener todos tus enlaces importantes, sin importar la plataforma, en un solo lugar.</p>
                    <p data-i18n="benefit3">3. Encontrar en segundos enlaces guardados en el historial o en los favoritos de tus aplicaciones, gracias a una biblioteca centralizada y a un motor de búsqueda efectivo.</p>
                    <p data-i18n="benefit4">4. Abrir los enlaces directamente para ver su contenido.</p>
                    <p data-i18n="benefit5">5. Copiar los enlaces guardados para compartirlos.</p>
					          <br>
                    <center><p style="color: blue; font-weight: bold; font-size: 24px;" data-i18n="conditionsHeading">Condiciones</p></center>
                    <br>
                    <p data-i18n="condition1">1. No incluyas información sensible ni personal. No nos hacemos responsables de la protección o del uso que terceros hagan de tu información.</p>
                    <p data-i18n="condition2">2. El tratamiento de los datos se realiza bajo la modalidad de <a href="HabeasData.pdf" target="_blank" rel="noopener" style="color: var(--accent) !important; text-decoration: underline;">Habeas Data</a>.</p>
                    <p data-i18n="condition3">3. No nos hacemos responsables del contenido que incite a la violencia física o psicológica, a delitos informáticos o a cualquier otra actividad delictiva.</p>
                    <p data-i18n="condition4">4. Opciones de precios de suscripción:</p>
                    <p data-i18n="priceThreeMonths"><span class="destacado">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$20.000</span> pesos colombianos (COP) por tres meses.</p>
                    <p data-i18n="priceSixMonths"><span class="destacado">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$40.000</span> pesos colombianos (COP) por seis meses.</p>
                    <p data-i18n="priceYear"><span class="destacado">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$60.000</span> pesos colombianos (COP) por un año.</p>
                    <p data-i18n="condition5">5. Si no renuevas tu suscripción y no deseas continuar con el servicio, tendrás un máximo de 3 meses calendario para solicitar que te enviemos tus enlaces y la información asociada en un archivo CSV. Envía la solicitud a <a href="mailto:adm@solicionespro.com">adm@solicionespro.com</a>. La información se enviará al correo electrónico registrado en la página.</p>
                    <br>
                    <center><p style="color: blue; font-weight: bold; font-size: 24px;" data-i18n="paymentHeading">Forma de Pago de Suscripción y Subcategorías</p></center>
                    <p data-i18n="payment1">1. Paga por Nequi al +57 305 4293185 o por PayPal a YSXRZMT2AAG4G.</p>
                    <p data-i18n="payment2">2. Toma una imagen del pago realizado y conviértela a formato PDF.</p>
                    <p data-i18n="payment3">3. Envía el archivo PDF o una imagen del pago desde la cuenta de correo con la que te registraste.</p>
                    <p data-i18n="payment4">4. Si tienes alguna duda, escribe al administrador a adm@solicionespro.com o por WhatsApp al +57 305 4293185.</p>
				        </div>
          </div>
          </center>
          <hr>
          <center><a id="headerfonts" href="requestaccessfinal.php" class="btn btn-danger" style="background-color: #9A97F5; color: black; font-weight: bold; margin-left: 10px; margin-right: 20px; border-color: #9A97F5 ;"><i class="fa fa-reply" aria-hidden="true"></i> <span data-i18n="backButton">Página Principal</span></a></center>
    </div>    
  </div>
    </body>

  <!-- BOOTSTRAP -->	
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous"></link>
    <script src="https://kit.fontawesome.com/60f0db780e.js" crossorigin="anonymous"></script>
    <script>
        const termsTranslations = {
            es: {
                pageTitle: "TÉRMINOS Y CONDICIONES",
                pageSubtitle: "Consideraciones y cubrimientos de Servicios",
                termsTitle: "Beneficios y Condiciones de Uso de la Aplicación",
                benefitsHeading: "Ventajas",
                benefitsIntro: "Gracias por usar la herramienta o la versión gratuita de 30 días calendario de la biblioteca de Contenidos Útiles, que te ayuda a:",
                benefit1: "1. Etiquetar el contenido con una breve descripción para que puedas encontrarlo fácilmente.",
                benefit2: "2. Tener todos tus enlaces importantes, sin importar la plataforma, en un solo lugar.",
                benefit3: "3. Encontrar en segundos enlaces guardados en el historial o en los favoritos de tus aplicaciones, gracias a una biblioteca centralizada y a un motor de búsqueda efectivo.",
                benefit4: "4. Abrir los enlaces directamente para ver su contenido.",
                benefit5: "5. Copiar los enlaces guardados para compartirlos.",
                conditionsHeading: "Condiciones",
                condition1: "1. No incluyas información sensible ni personal. No nos hacemos responsables de la protección o del uso que terceros hagan de tu información.",
                condition2: "2. El tratamiento de los datos se realiza bajo la modalidad de <a href=\"HabeasData.pdf\" target=\"_blank\" rel=\"noopener\" style=\"color: var(--accent) !important; text-decoration: underline;\">Habeas Data</a>.",
                condition3: "3. No nos hacemos responsables del contenido que incite a la violencia física o psicológica, a delitos informáticos o a cualquier otra actividad delictiva.",
                condition4: "4. Opciones de precios de suscripción:",
                priceThreeMonths: "<span class=\"destacado\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$20.000</span> pesos colombianos (COP) por tres meses.",
                priceSixMonths: "<span class=\"destacado\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$40.000</span> pesos colombianos (COP) por seis meses.",
                priceYear: "<span class=\"destacado\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$60.000</span> pesos colombianos (COP) por un año.",
                condition5: "5. Si no renuevas tu suscripción y no deseas continuar con el servicio, tendrás un máximo de 3 meses calendario para solicitar que te enviemos tus enlaces y la información asociada en un archivo CSV. Envía la solicitud a <a href=\"mailto:adm@solicionespro.com\">adm@solicionespro.com</a>. La información se enviará al correo electrónico registrado en la página.",
                paymentHeading: "Forma de Pago de Suscripción y Subcategorías",
                payment1: "1. Paga por Nequi al +57 305 4293185 o por PayPal a YSXRZMT2AAG4G.",
                payment2: "2. Toma una imagen del pago realizado y conviértela a formato PDF.",
                payment3: "3. Envía el archivo PDF o una imagen del pago desde la cuenta de correo con la que te registraste.",
                payment4: "4. Si tienes alguna duda, escribe al administrador a adm@solicionespro.com o por WhatsApp al +57 305 4293185.",
                backButton: "Página Principal"
            },
            en: {
                pageTitle: "TERMS AND CONDITIONS",
                pageSubtitle: "Service Considerations and Coverage",
                termsTitle: "Application Benefits and Terms of Use",
                benefitsHeading: "Benefits",
                benefitsIntro: "Thank you for using the tool or the 30-calendar-day free version of the Useful Content library, which helps you:",
                benefit1: "1. Tag content with a brief description so you can find it easily.",
                benefit2: "2. Keep all your important links, regardless of platform, in one place.",
                benefit3: "3. Find links saved in your app history or favorites in seconds, using a centralized library and an effective search engine.",
                benefit4: "4. Open links directly to view their content.",
                benefit5: "5. Copy saved links to share them.",
                conditionsHeading: "Terms",
                condition1: "1. Do not include sensitive or personal information. We are not responsible for protecting your information or for how third parties use it.",
                condition2: "2. Data is processed under the <a href=\"HabeasData.pdf\" target=\"_blank\" rel=\"noopener\" style=\"color: var(--accent) !important; text-decoration: underline;\">Habeas Data</a> framework.",
                condition3: "3. We are not responsible for content that incites physical or psychological violence, cybercrime, or any other criminal activity.",
                condition4: "4. Subscription pricing options:",
                priceThreeMonths: "<span class=\"destacado\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$20,000</span> Colombian pesos (COP) for three months.",
                priceSixMonths: "<span class=\"destacado\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$40,000</span> Colombian pesos (COP) for six months.",
                priceYear: "<span class=\"destacado\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$60,000</span> Colombian pesos (COP) for one year.",
                condition5: "5. If you do not renew your subscription and do not wish to continue the service, you have up to 3 calendar months to request your links and related information in a CSV file. Send your request to <a href=\"mailto:adm@solicionespro.com\">adm@solicionespro.com</a>. The information will be sent to the email address registered on the website.",
                paymentHeading: "Payment for Subscriptions and Subcategories",
                payment1: "1. Pay via Nequi to +57 305 4293185 or via PayPal to YSXRZMT2AAG4G.",
                payment2: "2. Take an image of the payment and convert it to PDF format.",
                payment3: "3. Email the PDF file or an image of the payment from the email account you used to register.",
                payment4: "4. If you have any questions, email the administrator at adm@solicionespro.com or contact them on WhatsApp at +57 305 4293185.",
                backButton: "Home Page"
            },
            pt: {
                pageTitle: "TERMOS E CONDIÇÕES",
                pageSubtitle: "Considerações e Cobertura dos Serviços",
                termsTitle: "Benefícios e Condições de Uso do Aplicativo",
                benefitsHeading: "Vantagens",
                benefitsIntro: "Agradecemos por usar a ferramenta ou a versão gratuita de 30 dias corridos da biblioteca de Conteúdos Úteis, que ajuda você a:",
                benefit1: "1. Identificar o conteúdo com uma breve descrição para encontrá-lo facilmente.",
                benefit2: "2. Manter todos os seus links importantes, independentemente da plataforma, em um só lugar.",
                benefit3: "3. Encontrar em segundos links salvos no histórico ou nos favoritos dos seus aplicativos, graças a uma biblioteca centralizada e a um mecanismo de busca eficiente.",
                benefit4: "4. Abrir os links diretamente para visualizar o conteúdo.",
                benefit5: "5. Copiar os links salvos para compartilhá-los.",
                conditionsHeading: "Condições",
                condition1: "1. Não inclua informações confidenciais ou pessoais. Não nos responsabilizamos pela proteção das suas informações nem pelo uso que terceiros fizerem delas.",
                condition2: "2. Os dados são tratados de acordo com a modalidade <a href=\"HabeasData.pdf\" target=\"_blank\" rel=\"noopener\" style=\"color: var(--accent) !important; text-decoration: underline;\">Habeas Data</a>.",
                condition3: "3. Não nos responsabilizamos por conteúdos que incitem violência física ou psicológica, crimes cibernéticos ou qualquer outra atividade criminosa.",
                condition4: "4. Opções de preços da assinatura:",
                priceThreeMonths: "<span class=\"destacado\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$20.000</span> pesos colombianos (COP) por três meses.",
                priceSixMonths: "<span class=\"destacado\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$40.000</span> pesos colombianos (COP) por seis meses.",
                priceYear: "<span class=\"destacado\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$60.000</span> pesos colombianos (COP) por um ano.",
                condition5: "5. Se você não renovar a assinatura e não quiser continuar usando o serviço, terá no máximo 3 meses corridos para solicitar o envio dos seus links e das informações relacionadas em um arquivo CSV. Envie a solicitação para <a href=\"mailto:adm@solicionespro.com\">adm@solicionespro.com</a>. As informações serão enviadas ao endereço de email cadastrado no site.",
                paymentHeading: "Forma de Pagamento de Assinaturas e Subcategorias",
                payment1: "1. Pague pelo Nequi no número +57 305 4293185 ou pelo PayPal para YSXRZMT2AAG4G.",
                payment2: "2. Tire uma imagem do pagamento realizado e converta-a para o formato PDF.",
                payment3: "3. Envie o arquivo PDF ou uma imagem do pagamento usando a conta de email com a qual você se cadastrou.",
                payment4: "4. Em caso de dúvidas, envie um email para o administrador: adm@solicionespro.com, ou entre em contato pelo WhatsApp: +57 305 4293185.",
                backButton: "Página Inicial"
            }
        };

        function applyTermsTranslations(language) {
            const selectedLanguage = termsTranslations[language] ? language : "es";
            const translations = termsTranslations[selectedLanguage];
            document.querySelectorAll("[data-i18n]").forEach(element => {
                const key = element.getAttribute("data-i18n");
                if (translations[key] !== undefined) {
                    element.innerHTML = translations[key];
                }
            });
            document.documentElement.lang = selectedLanguage;
        }

        applyTermsTranslations(localStorage.getItem("smartshelfLang") || "es");
        window.addEventListener("storage", event => {
            if (event.key === "smartshelfLang") {
                applyTermsTranslations(event.newValue || "es");
            }
        });
    </script>

    </html>