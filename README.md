dynamicscrm-connector
=====================

Symfony package for Dynamics CRM connection on NTLM

This bundle will help to connect to Microsoft CRM 2011 in NTLM 
Based on Curl this will allow the Usage of standard commands :

$DynamicsCrm=new DynamicsCrm($ServAdress, $User, $Password);

Retrieve

$DynamicsCrm->Retrieve($Table, $Id, $Columns);

RetrieveMultiple

$result=$DynamicsCrm->RetrieveMultiple($table,$Where, $Columns,$Join,$Order);

Update

$DynamicsCrm->Update($Table, $Params, $Id);

Create

$DynamicsCrm->Create($Table, $Params);

Delete

$DynamicsCrm->Delete($Table, $Id);

#############################################################################

Installation

if by any mean you dont get the bundle from packagist add to you composer.json under require\n

 "dynamicscrm/connector" : "dev-master"
to you composer.json
and then : 
composer install
or

  composer update
enjoy the bundle

