<h3>Database: {$DB_Name}</h3>
{ if $DB_ProxyID }
<strong>Listener settings</strong><br/>
Name: {$DB_ProxyName}<br/>
Frontend: {$DB_Listener}<br/>
Backend:  {$DB_Backend}<br/>
Status:   {$DB_Status}<br/>
Type:     {$DB_Type}<br/>
<a href="proxy_add.php?proxyid={$DB_ProxyID}">Change listener settings</a><br/>
<br/>
<br/>
{ /if }
<strong>Database permissions:</strong><br/>
Change database structure: {$DB_Alter}<br/>
Create command: {$DB_Create}<br/>
Disclose table stucture: {$DB_Info}<br/>
Drop command:  {$DB_Drop}<br/>
Other sensitive commands: {$DB_BlockQ}<br/>
<a href="db_edit.php?id={$DB_ID}">Change database settings</a><br/>
