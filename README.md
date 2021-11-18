# TS-Data-fetcher
This is a set of scripts for monitoring users on a TeamSpeak server. 
- index.php is your landing page. It decides whether to respond with JSON to a POST request or to redirect user to "web monitor" in case of GET request (half-arsed API, basically).
- stats.php is a "web monitor" that allows you to see who is now online on your TS server.

All user visible text is in russian, but there's like 4 strings in total, so you can easily customize it to your desires.
Channel distribution of users is not supported, you can only see who is now on the server and for how long are they connected.
