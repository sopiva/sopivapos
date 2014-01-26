attr = lighty.stat(lighty.env["physical.path"])
if (not attr) then
  local request_uri = lighty.env["uri.path"]
  local uriquery = lighty.env["uri.query"] or ""
  lighty.env["uri.query"] = uriquery .. (uriquery ~= "" and "&" or "") .. "url=" .. request_uri
  lighty.env["uri.path"] = "/index.php"
  lighty.env["physical.rel-path"] = lighty.env["uri.path"]
  lighty.env["physical.path"] = lighty.env["physical.doc-root"] .. lighty.env["physical.rel-path"]
end
