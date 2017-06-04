var url = 'http://89.223.25.199/server/server1/';
var url1 = 'http://89.223.25.199/server/server2/';

function doRequest(url) {
  https.get(url, (res) => {
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

setInterval(doRequest(url), 5000);
setInterval(doRequest(url1), 5000);