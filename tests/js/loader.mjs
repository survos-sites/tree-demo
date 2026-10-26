// Node stand-in for AssetMapper's importmap: assets/router.js imports the bare
// '@symfony/ux-router', which AssetMapper maps to the package's dist file.
import { registerHooks } from 'node:module';
import { pathToFileURL } from 'node:url';

const UX_ROUTER = pathToFileURL(process.env.UX_ROUTER_DIST).href;

registerHooks({
  resolve(specifier, context, next) {
    if (specifier === '@symfony/ux-router') {
      return { url: UX_ROUTER, format: 'module', shortCircuit: true };
    }
    return next(specifier, context);
  },
});
