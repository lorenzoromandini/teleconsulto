<?php

require "config.php";

$postjson = json_decode(file_get_contents('php://input'), true);

if ($postjson['request'] == "process_register") { 
 
    $checkemail = mysqli_fetch_array(mysqli_query($mysqli, "SELECT * FROM medico WHERE email = '$postjson[email]'"));

    if ($checkemail) {
        $result = json_encode(array('success' => false, 'msg' => "Email già registrata"));
    } else {

        $password = md5($postjson['password']);

        $insert = mysqli_query($mysqli, "INSERT INTO medico SET
    codice_fiscale = '$postjson[codice_fiscale]',
    nome = '$postjson[nome]',
    cognome = '$postjson[cognome]',
    data_nascita = '$postjson[data_nascita]',
    professione = '$postjson[professione]',
    email = '$postjson[email]',
    gender = '$postjson[gender]',
    password = '$password'
    ");

        if ($insert) {
            $result = json_encode(array('success' => true, 'msg' => "Registrazione completata"));
        } else {
            $result = json_encode(array('success' => false, 'msg' => "Errore nella registrazione"));
        }
    }

    echo $result;
} elseif ($postjson['request'] == "process_login") {

    $password = md5($postjson['password']);

    $logindata = mysqli_fetch_array(mysqli_query($mysqli, "SELECT * FROM medico WHERE email = '$postjson[email]' AND password = '$password' "));

    $data = array(
        'id' => $logindata['id'],
        'codice_fiscale' => $logindata['codice_fiscale'],
        'nome' => $logindata['nome'],
        'cognome' => $logindata['cognome'],
        'professione' => $logindata['professione'],
        'gender' => $logindata['gender'],
        'data_nascita' => $logindata['data_nascita'],
        'email' => $logindata['email'],
        'password' => $password
    );

    if ($logindata) {
        $result = json_encode(array('success' => true, 'result' => $data));
    } else {
        $result = json_encode(array('success' => false));
    }

    echo $result;
} elseif ($postjson['request'] == "load_consulti_medico") {

    $query = mysqli_query($mysqli, "SELECT DISTINCT consulto.id AS id_consulto, oggetto, data_inizio, paziente.nome AS paziente_nome, 
    paziente.cognome AS paziente_cognome, paziente.codice_fiscale AS paziente_cf, paziente.data_nascita AS paziente_data_nascita, 
    medico.nome AS richiedente_nome, medico.cognome AS richiedente_cognome FROM partecipanti p, partecipanti q, consulto, paziente, medico 
    WHERE p.id_medico = '$postjson[id_medico]' AND p.id_consulto = consulto.id AND consulto.paziente = paziente.id AND q.id_consulto = consulto.id 
    AND q.richiedente = true AND medico.id = q.id_medico ORDER BY paziente.cognome, paziente.nome, paziente.codice_fiscale;");

    while ($rows = mysqli_fetch_array($query)) {

        $data[] = array(
            'oggetto' => $rows['oggetto'],
            'data_inizio' => $rows['data_inizio'],
            'paziente_nome' => $rows['paziente_nome'],
            'paziente_cognome' => $rows['paziente_cognome'],
            'paziente_cf' => $rows['paziente_cf'],
            'paziente_data_nascita' => $rows['paziente_data_nascita'],
            'id_consulto' => $rows['id_consulto'],
            'richiedente_nome' => $rows['richiedente_nome'],
            'richiedente_cognome' => $rows['richiedente_cognome'],
        );
    }

    if ($query) {
        $result = json_encode(array('success' => true, 'result' => $data));
    } else {
        $result = json_encode(array('success' => false));
    }

    echo $result;
} elseif ($postjson['request'] == "load_partecipanti") {

    $query = mysqli_query($mysqli, "SELECT medico.cognome AS medico_cognome, medico.nome AS medico_nome 
    FROM consulto, medico, partecipanti WHERE consulto.id = '$postjson[id_consulto]' AND consulto.id = partecipanti.id_consulto 
    AND partecipanti.id_medico = medico.id ORDER BY medico_cognome ");

    while ($rows = mysqli_fetch_array($query)) {

        $data[] = array(
            'medico_cognome' => $rows['medico_cognome'],
            'medico_nome' => $rows['medico_nome'],
        );
    }

    if ($query) {
        $result = json_encode(array('success' => true, 'result' => $data));
    } else {
        $result = json_encode(array('success' => false));
    }

    echo $result;
} elseif ($postjson['request'] == "update_profile") {

    $checkemail = mysqli_fetch_array(mysqli_query($mysqli, "SELECT email FROM medico WHERE id != '$postjson[id]' "));

    if ($checkemail['email'] == $postjson['email']) {
        $result = json_encode(array('success' => false, 'msg' => "Email già registrata"));
    } else {

        if ($postjson['passwordInput'] == "") {
            $password = $postjson['passwordCurrent'];
        } else {
            $password = md5($postjson['passwordInput']);
        }

        $update = mysqli_query($mysqli, "UPDATE medico SET
    codice_fiscale = '$postjson[codice_fiscale]',
    nome = '$postjson[nome]',
    cognome = '$postjson[cognome]',
    data_nascita = '$postjson[data_nascita]',
    professione = '$postjson[professione]',
    email = '$postjson[email]',
    gender = '$postjson[gender]',
    password = '$password'

    WHERE id = '$postjson[id]' ");

        if ($update) {
            $result = json_encode(array('success' => true, 'msg' => "Profilo aggiornato"));
        } else {
            $result = json_encode(array('success' => false, 'msg' => "Errore"));
        }
    }

    echo $result;
} elseif ($postjson['request'] == "search_partecipants") {

    $query = mysqli_query($mysqli, "SELECT id, cognome, nome, codice_fiscale, professione FROM medico WHERE cognome LIKE '%$postjson[medico_cognome]%' 
    AND nome LIKE '%$postjson[medico_nome]%' AND professione LIKE '%$postjson[medico_professione]%' ORDER BY cognome, nome; ");

    while ($rows = mysqli_fetch_array($query)) {

        $data[] = array(
            'id' => $rows['id'],
            'cognome' => $rows['cognome'],
            'nome' => $rows['nome'],
            'codice_fiscale' => $rows['codice_fiscale'],
            'professione' => $rows['professione'],
        );
    }

    if ($query) {
        $result = json_encode(array('success' => true, 'result' => $data));
    } else {
        $result = json_encode(array('success' => false));
    }

    echo $result;
} elseif ($postjson['request'] == "search_paziente") {

    $query = mysqli_query($mysqli, "SELECT id, cognome, nome, codice_fiscale, data_nascita FROM paziente 
    WHERE cognome LIKE '%$postjson[paziente_cognome]%' AND nome LIKE '%$postjson[paziente_nome]%' ORDER BY cognome, nome; ");

    while ($rows = mysqli_fetch_array($query)) {

        $data[] = array(
            'id' => $rows['id'],
            'cognome' => $rows['cognome'],
            'nome' => $rows['nome'],
            'codice_fiscale' => $rows['codice_fiscale'],
            'data_nascita' => $rows['data_nascita'],
        );
    }

    if ($query) {
        $result = json_encode(array('success' => true, 'result' => $data));
    } else {
        $result = json_encode(array('success' => false));
    }

    echo $result;
} elseif ($postjson['request'] == "new_paziente") {

    $paziente = mysqli_fetch_array(mysqli_query($mysqli, "SELECT * FROM paziente WHERE id = '$postjson[id_paziente]' "));

    $data = array(
        'id' => $paziente['id'],
        'codice_fiscale' => $paziente['codice_fiscale'],
        'nome' => $paziente['nome'],
        'cognome' => $paziente['cognome'],
        'data_nascita' => $paziente['data_nascita']
    );

    if ($paziente) {
        $result = json_encode(array('success' => true, 'result' => $data));
    } else {
        $result = json_encode(array('success' => false));
    }

    echo $result;
} elseif ($postjson['request'] == "new_partecipante") {

    $partecipante = mysqli_fetch_array(mysqli_query($mysqli, "SELECT * FROM medico WHERE id = '$postjson[id_medico]' "));

    $data = array(
        'id' => $partecipante['id'],
        'codice_fiscale' => $partecipante['codice_fiscale'],
        'nome' => $partecipante['nome'],
        'cognome' => $partecipante['cognome'],
        'professione' => $partecipante['professione']
    );

    if ($partecipante) {
        $result = json_encode(array('success' => true, 'result' => $data));
    } else {
        $result = json_encode(array('success' => false));
    }

    echo $result;
} elseif ($postjson['request'] == "load_messages") {

    $query = mysqli_query($mysqli, "SELECT medico.id AS mittente_id, medico.nome AS mittente_nome, medico.cognome AS mittente_cognome, testo, data_ora 
    FROM messaggi, medico WHERE id_consulto = '$postjson[id_consulto]' AND medico.id = messaggi.id_medico ORDER BY data_ora");

    while ($rows = mysqli_fetch_array($query)) {

        $data[] = array(
            'mittente_id' => $rows['mittente_id'],
            'mittente_nome' => $rows['mittente_nome'],
            'mittente_cognome' => $rows['mittente_cognome'],
            'testo' => $rows['testo'],
            'data_ora' => $rows['data_ora'],
        );
    }

    if ($query) {
        $result = json_encode(array('success' => true, 'result' => $data));
    } else {
        $result = json_encode(array('success' => false));
    }

    echo $result;
} elseif ($postjson['request'] == "send_message") {

    $insert = mysqli_query($mysqli, "INSERT INTO messaggi SET
    id_medico = '$postjson[id_utente]',
    id_consulto = '$postjson[id_consulto]',
    testo = '$postjson[testo]'
    ");

    if ($insert) {
        $result = json_encode(array('success' => true));
    } else {
        $result = json_encode(array('success' => false, 'msg' => "Errore nell'invio del messaggio"));
    }

    echo $result;
} elseif ($postjson['request'] == "delete_message") {

    $query = mysqli_query($mysqli, "DELETE * FROM messaggi WHERE messaggi.id_medico = '$postjson[id]' ");

    if ($query) {
        $result = json_encode(array('success' => true));
    } else {
        $result = json_encode(array('success' => false));
    }

    echo $result;
}