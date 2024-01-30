<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require_once(dirname(__FILE__) . '/../lib/PHPMailer/src/Exception.php');
require_once(dirname(__FILE__) . '/../lib/PHPMailer/src/PHPMailer.php');
require_once(dirname(__FILE__) . '/../lib/PHPMailer/src/SMTP.php');
require_once(dirname(__FILE__) . '/CdL.php');

class InvioProspetto
{
    private string $mail;
    private string $matricola;
    private string $CdL;


    public function __construct($mail, $matricola, $CdL)
    {
        $this->mail = $mail;
        $this->matricola = $matricola;
        $this->CdL = $CdL;
    }

    public function inviaEmail(): bool
    {
        $mail = new PHPMailer();
        try {
            $mail->IsSMTP();
            $mail->Host = "mixer.unipi.it";
            $mail->SMTPSecure = "tls";
            $mail->SMTPAuth = false;
            $mail->Port = 25;


            $mail->From = 'no-reply-laureandosi@ing.unipi.it';
            $mail->AddAddress($this->mail);
            $mail->Subject = 'Appello di laurea in ' . $this->CdL . '- indicatori per voto di laurea';
            $mail->Body = stripslashes(
                "Gentile laureando/laureanda,
                                    Allego un prospetto contenente: la sua carriera, gli indicatori e la formula che la commissione adoperera' per
                                    determinare il voto di laurea.
                                    \nLa prego di prendere visione dei dati relativi agli esami.
                                    \nIn caso di dubbi scrivere a: vittoria.dattilo@unipi.it
                                    \n\nAlcune spiegazioni:
                                    \n- gli esami che non hanno un voto in trentesimi, hanno voto nominale zero al posto di giudizio o idoneita', in
                                    quanto non contribuiscono al calcolo della media ma solo al numero di crediti curriculari;
                                    \n- gli esami che non fanno media (pur contribuendo ai crediti curriculari) non hanno la spunta nella colonna MED;
                                    \n- il voto di tesi (T) appare nominalmente a zero in quanto verra' determinato in sede di laurea, e va da 18 a 30.
                                    \n\nCordiali saluti
                                    \nUnita' Didattica DII"
            );

            $mail->addAttachment(
                dirname(__FILE__) . "/../prospetti/" . CdL::getCdLcorto(
                    $this->CdL
                ) . "/" . $this->matricola . "_laureando.pdf"
            );

            return ($mail->Send());
        } catch (Exception) {
            echo "Errore";
            $mail->SmtpClose();
        }
        return false;
    }

}