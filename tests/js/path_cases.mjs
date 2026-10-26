// Usage: UX_ROUTER_DIST=... ROUTING_MODULE=... node --import ./loader.mjs path_cases.mjs cases.json
// Prints {"<case id>": "<url>" | {"error": "..."}} for each case, using the JS path().
import { readFileSync } from 'node:fs';
import { pathToFileURL } from 'node:url';

const { path } = await import(pathToFileURL(process.env.ROUTING_MODULE).href);
const cases = JSON.parse(readFileSync(process.argv[2], 'utf8'));
const out = {};
for (const [id, { route, params }] of Object.entries(cases)) {
  try {
    out[id] = path(route, params);
  } catch (e) {
    out[id] = { error: e.message };
  }
}
console.log(JSON.stringify(out));
