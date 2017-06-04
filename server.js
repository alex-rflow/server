var http = require('http');
var server = http.createServer(function (request, response) {
  response.writeHead(200, {"Content-Type": "text/plain"});
  response.end("Hello World\n");
});
server.listen(25575);
console.log("Server running at http://127.0.0.1:25575/");

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

setInterval(function() {doRequest(url)}, 2000);