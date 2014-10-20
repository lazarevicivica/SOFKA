function keep_alive() {
    http_request = new XMLHttpRequest();
    http_request.open('GET', "/site/keep_alive");
    http_request.send(null);
};

