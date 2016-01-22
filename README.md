# snmp-interfacegraph
Simple PHP and Javascript page for drawing graphs based on interface usage from SNMP

# Instructions
1.  Install on a PHP server with php snmp enabled
2.  Configuring the $hosts array in snmpGet.php to include the hosts you want on your network to be able to monitor.
3.  If desiered you can change some variable in index.htm to change the graph length, type and interval 
4.  Open your browser and point it index.htm
5.  Click on the host
6.  Clikc on the interface
7.  Watch your graph!

The script uses URL hashes to allow you to bookmark and frame in the pages for specific graphs. It works great with iFrames in a single pane of glass setup.  

# iFrame Example
`<iframe src="//server/index.htm#hostname,ifnum,ifname" width="400" height="200" frameborder="0"></iframe>`

