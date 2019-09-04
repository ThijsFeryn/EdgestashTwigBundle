vcl 4.0;
import edgestash;
import std;

backend default
{
    .host = "origin"; //Replace with the hostname of your origin server
    .port = "80"; //Optionally replace with the HTTP port number of your origin server
}

sub vcl_recv
{
    set req.http.Surrogate-Capability={"edgestash="EDGESTASH/2.1""};

    if ((req.method != "GET" && req.method != "HEAD") || req.http.Authorization) {
        return (pass);
    }

    return(hash);
}

sub vcl_backend_response
{
    if(beresp.http.Link) {
        std.collect(beresp.http.Link,",");
    }

    if(beresp.http.Link ~ "<([^>]+)>; rel=edgestash") {
        set beresp.http.x-edgestash-json-urls = regsuball(beresp.http.Link,"(?(?=<[^>]+>; rel=edgestash)<([^>]+)>; rel=edgestash|<([^>]+)>; rel=[a-z]+, )","\1");
    }

    if(beresp.http.Surrogate-Control) {
        std.collect(beresp.http.Surrogate-Control);
    }

    if(beresp.http.Surrogate-Control ~ {".*="EDGESTASH/2\.[0-9]+".*"}) {
        edgestash.parse_response();
    }
}

sub vcl_deliver
{
   if(edgestash.is_edgestash() && resp.http.x-edgestash-json-urls) {
        edgestash.add_json_url_csv(resp.http.x-edgestash-json-urls);
        edgestash.execute();
    }

    unset resp.http.Link;
    unset resp.http.x-edgestash-json-urls;
    unset resp.http.surrogate-control;
}