(function (d, s, id) { var js, yjs = d.getElementsByTagName(s)[0]; if (d.getElementById(id)) return; js = d.createElement(s); js.id = id; js.src = "https://api."+yawavesdkobject.sdk_domain+"/public/v1/open/sdk/init?clientId="+yawavesdkobject.sdk_client_id+"&lang="+yawavesdkobject.sdk_language_code; yjs.parentNode.insertBefore(js, yjs); }(document, 'script', 'yawave-jssdk'));