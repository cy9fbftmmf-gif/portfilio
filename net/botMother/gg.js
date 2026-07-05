// Controle.js

function verifier()
{
    let pseudo = document.getElementById("pseudo").value;
    let logiciel = document.getElementById("logiciel").value;
    let note = document.getElementById("note").value;

    
    let reg = /^[a-zA-Z0-9]{6}$/;

    if(!reg.test(pseudo))
    {
        alert("Le pseudo doit contenir 6 caractères alphanumériques");
        return false;
    }

    
    if(logiciel == "")
    {
        alert("Veuillez choisir un logiciel");
        return false;
    }

    
    if(note == "" || isNaN(note) || note < 0 || note > 10)
    {
        alert("La note doit être un entier entre 0 et 10");
        return false;
    }

    alert("Formulaire valide !");
    return true;
}