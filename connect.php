<?php
// Variaveis Globais
#$ldap_conn      = getenv($conn);
$ldap_conn      = "ldaps://172.16.0.52:636";
#$dominio        = getenv($dom); /*Nome Netbios do dominio*/
$dominio        = "tj.pa.gov.br"; /*Nome Netbios do dominio*/

date_default_timezone_set('America/Belem');

// Usuario para autenticacao na Base LDAP
$auth_user      = getenv("user");
$auth_pass      = getenv("pass");

// Base de pesquisa LDAP do Dominio
#$base_dn = getenv($dn);
$base_dn = "DC=i,DC=tj,DC=pa,DC=gov,DC=br";

// Conecta no servidor
$connect = ldap_connect($ldap_conn) or die( "Could not connect!" );

// Define que a pesquisa será feita em a partir do diretório raiz do domínio.
ldap_set_option ($connect, LDAP_OPT_REFERRALS, 0);

// Define a versão do protocolo LDAP
ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, 3) or die ("Could not set ldap protocol");

// Autentica no servidor
$bind = ldap_bind($connect, $auth_user, $auth_pass) or die ("Could not bind");

?>
