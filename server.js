const http = require('http');

var url = 'http://89.223.25.199/server/server1/';
var url1 = 'http://89.223.25.199/server/server2/';

function doRequest(url) {
  http.get(url, (res) => {
    if (res.statusCode >= 300 && res.statusCode <= 400 && res.headers.location) {
      doRequest(res.headers.location);
    }
    res.on('data', (d) => {
      process.stdout.write(d);
    });

  }).on('error', (e) => {
    console.error(e);
  });
}

function start() {
	doRequest(url);
	doRequest(url1);
}

setInterval(start(), 5000);