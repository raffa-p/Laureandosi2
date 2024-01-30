let msg = document.getElementById("feedback");
let bottone_crea = document.getElementById("creaProspetti");

bottone_crea.addEventListener('click', crea);

function crea(e){
    if(e!== undefined){
        e.preventDefault();
    }
    fetch('/../classi/GestoreRichiesteInterfaccia.php', {
        method: 'POST',
        headers: {"Content-Type" : "application/x-www-form-urlencoded"},
        body: "CdL=" + document.querySelector('select[name="CdL"]').value
            + "&matricole=" + document.querySelector('textarea[name="matricole"]').value
            + "&dataLaurea=" + document.querySelector('input[name="dataLaurea"]').value
            + "&Type=crea"
    })
        .then(response => {
            if(response.ok && response.status === 202){
                return Promise.reject("Finito");
            }
            if(response.status === 502){
                return Promise.reject("Errore");
            }
            if(response.ok && response.status === 203){
                return Promise.reject("NoInfo");
            }
            return response.text();
        })
        .then(text => {
            if(text === "Errore"){
                msg.style.color = "#dc3603";
                return Promise.reject("Errore");
            }
            else{
                msg.style.color = "#000000"
            }
            msg.innerText = text;
        })
        .catch(error => {
            if (error === "Finito") {
                msg.innerText = "Prospetti creati";
                msg.style.color = "#00a32a";
            } else{
                msg.style.color = "#dc3603";
                if(error === "Errore") {
                    msg.innerText = "Errore";
                }
                else{
                    msg.innerText = "Informazioni mancanti";
                }
            }
        });
}