UKMfestivalen
=============

UKM Festivalen representerer festivalen som arrangeres av UKM Norge.

UKM Festivalen i systemet behandles som arrangement av type `land` <br>
Festivalen har ikke åpen påmelding men tar i mot arrangementer fra fylker

Festival nettsiden kan finnes her `https://ukm.no/festivalen/wp-admin`

Hvert år må en ny festival opprettes:
1. i databasen må denne sql-spørringen kjøres:
``` sql
INSERT IGNORE INTO `smartukm_place` (`pl_start`, `pl_stop`, `pl_public`, `pl_missing`, `pl_form`, `pl_type`, `pl_deadline`, `pl_deadline2`, `pl_forward_start`, `pl_forward_stop`, `pl_owner_kommune`, `pl_owner_fylke`, `old_pl_fylke`, `old_pl_kommune`, `pl_name`, `season`) VALUES ('0', '0', '0', '0', '0', 'land', '2022-01-01 23:59:59', '2022-01-01 23:59:59', '2022-01-01 23:59:59', '2022-01-01 23:59:59', '0', '0', '123456789', '123456789', 'UKM-Festivalen', '2022') 
```
OBS: Husk å bytte sessong 2022 med nåværende år.<br><br>
2. Gå på `https://ukm.no/wp-admin/network/sites.php`<br>
3.  Bruk søk feltet og lett etter `festivalen`<br>
4. Velg `ukm.no/festivalen`<br>
5. Velg `Innstillinger`<br>
6. Skriv ny festival id fra <b>steg 1</b> i feltet som heter `Pl Id`<br>