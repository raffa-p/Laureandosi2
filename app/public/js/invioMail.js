let messaggio = document.getElementById("feedback");
let bottone_invia = document.getElementById("inviaMail");

bottone_invia.addEventListener('click', invia);

function invia(e){
    if(e!== undefined){
        e.preventDefault();
    }
    fetch('/../classi/GestoreRichiesteInterfaccia.php', {
        method: 'POST',
        headers: {"Content-Type" : "application/x-www-form-urlencoded"},
        body: "CdL=" + document.querySelector('select[name="CdL"]').value
            + "&Type=invio"
    })
        .then(response => {
            if(response.ok && response.status === 203){
                return Promise.reject("Finito");
            }
            if(response.ok && response.status === 204){
                return Promise.reject("Vuoto");
            }
            if(response.status === 502){
                return Promise.reject("Errore");
            }
            if(response.ok && response.status !== 203){
                setTimeout(invia, 3000);
            }
            return response.text();
        })
        .then(text => {
            if(text === "Errore" || text === "Vuoto"){
                messaggio.style.color = "#dc3603";
                return Promise.reject("Errore");
            }
            else{
                messaggio.style.color = "#000000"
            }
            messaggio.innerText = text;
        })
        .catch(error => {
            if (error === "Finito") {
                messaggio.innerText = "Invio completato";
                messaggio.style.color = "#00a32a";
            } else{
                if(error === "Vuoto"){
                    messaggio.style.color = "#dc3603";
                    messaggio.innerText = "Coda vuota";
                }
                else {
                    messaggio.style.color = "#dc3603";
                    messaggio.innerText = "Errore invio";
                }
            }
        });
}