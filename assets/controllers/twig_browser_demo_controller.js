import { Controller } from '@hotwired/stimulus';
import { createEngine } from '@survos/twig-browser/createEngine';
import { installSymfonyTwigAPI } from '@survos/twig-browser/adapters/symfony';
import {
    detailContextHeaderCases,
    detailContextHeaderTemplate,
} from '@survos/twig-browser/testing/detailContextHeader';

function normalizeHtmlForCompare(html) {
    return html
        .replace(/>\s+/g, '>')
        .replace(/\s+</g, '<')
        .replace(/\s+/g, ' ')
        .replace(/> </g, '><')
        .replace(/&gt;/g, '>')
        .replace(/&lt;/g, '<')
        .replace(/&quot;/g, '"')
        .replace(/&#39;/g, "'")
        .trim();
}

function prettyTwigSource(source) {
    const lines = source
        .replace(/\r\n/g, '\n')
        .split('\n')
        .map((line) => line.replace(/\s+$/g, ''));

    while (lines.length && lines[0] === '') {
        lines.shift();
    }
    while (lines.length && lines[lines.length - 1] === '') {
        lines.pop();
    }

    const indents = lines
        .filter((line) => line.trim() !== '')
        .map((line) => line.match(/^\s*/)?.[0].length ?? 0);
    const minIndent = indents.length ? Math.min(...indents) : 0;

    return lines.map((line) => line.slice(minIndent)).join('\n');
}

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['status', 'source', 'vars', 'rendered', 'cases', 'htmlModal', 'htmlModalBody'];

    static values = {
        routeMap: { type: Object, default: {} },
    };

    connect() {
        this.loggedFailureNames = new Set();
        this.engine = this.createEngine();
        this.engine.compileBlock('detailContextHeader', detailContextHeaderTemplate);
        this.templateSource = prettyTwigSource(detailContextHeaderTemplate);

        this.results = detailContextHeaderCases.map((testCase, index) => {
            const actual = this.engine.renderBlock('detailContextHeader', testCase.vars);
            const expectedNormalized = normalizeHtmlForCompare(testCase.expected);
            const actualNormalized = normalizeHtmlForCompare(actual);
            const pass = actualNormalized === expectedNormalized;

            return {
                ...testCase,
                index,
                actual,
                expectedNormalized,
                actualNormalized,
                pass,
            };
        });

        this.sourceTarget.textContent = this.templateSource;
        this.renderCasesTable();
        this.logFailuresToConsole();

        const firstFailure = this.results.find((item) => !item.pass);
        this.selectCase(firstFailure ? firstFailure.index : 0);
    }

    logFailuresToConsole() {
        const failed = this.results.filter((item) => !item.pass);
        if (failed.length === 0) {
            console.info('[twig-browser-demo] All render cases passing.');
            return;
        }

        console.group(`[twig-browser-demo] ${failed.length} failing render case(s)`);
        failed.forEach((item) => {
            this.logSingleFailure(item);
        });
        console.groupEnd();
    }

    logSingleFailure(item) {
        if (this.loggedFailureNames.has(item.name)) {
            return;
        }
        this.loggedFailureNames.add(item.name);

        console.group(`FAIL: ${item.name}`);
        console.log('Vars:', item.vars);
        console.log('Twig source:', this.templateSource);
        console.log('Expected HTML:', item.expected);
        console.log('Actual HTML:', item.actual);
        console.log('Expected normalized:', item.expectedNormalized);
        console.log('Actual normalized:', item.actualNormalized);
        console.groupEnd();
    }

    showHtmlOutput() {
        if (window.bootstrap?.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(this.htmlModalTarget).show();
            return;
        }

        this.statusTarget.textContent = 'Bootstrap modal is not available.';
    }

    chooseCase(event) {
        const button = event.target.closest('[data-case-index]');
        if (!button) {
            return;
        }

        const index = Number(button.dataset.caseIndex);
        this.selectCase(index);
    }

    createEngine() {
        const engine = createEngine();

        installSymfonyTwigAPI(engine, {
            pathGenerator: (route, params = {}) => this.generatePath(route, params),
            uxIconResolver: (name, attrs = {}) => `<i class="bi bi-${name} ${attrs.class ?? 'text-primary'}" aria-hidden="true"></i>`,
        });

        return engine;
    }

    generatePath(route, params) {
        const routeMap = this.routeMapValue || {};
        const base = routeMap[route];
        if (!base) {
            throw new Error(`Missing route in routeMap value: ${route}`);
        }

        const url = new URL(base, window.location.origin);
        Object.entries(params).forEach(([key, value]) => {
            if (value !== undefined && value !== null) {
                url.searchParams.set(key, String(value));
            }
        });

        return `${url.pathname}${url.search}`;
    }

    renderCasesTable() {
        const escapedTemplate = this.escapeHtml(this.templateSource);
        const rows = this.results
            .map((result) => {
                const status = result.pass
                    ? '<span class="badge text-bg-success">PASS</span>'
                    : '<span class="badge text-bg-danger">FAIL</span>';

                return `
                    <tr>
                        <td class="text-nowrap">${status}</td>
                        <td><code>${result.name}</code></td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-secondary" data-case-index="${result.index}">Open</button>
                        </td>
                    </tr>
                    <tr class="table-light">
                        <td colspan="3" class="pt-1 pb-2">
                            <details>
                                <summary class="small text-secondary">Twig source</summary>
                                <pre class="small border rounded bg-white text-dark p-2 mt-2 mb-0" style="max-height: 8rem; overflow: auto; white-space: pre-wrap;">${escapedTemplate}</pre>
                            </details>
                        </td>
                    </tr>
                `;
            })
            .join('');

        this.casesTarget.innerHTML = `
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr><th>Status</th><th>Case</th><th></th></tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>
        `;
    }

    selectCase(index) {
        const result = this.results[index];
        if (!result) {
            return;
        }

        this.casesTarget
            .querySelectorAll('button[data-case-index]')
            .forEach((button) => button.classList.toggle('btn-primary', Number(button.dataset.caseIndex) === index));

        this.varsTarget.textContent = JSON.stringify(result.vars, null, 2);
        this.renderedTarget.innerHTML = result.actual;
        this.htmlModalBodyTarget.textContent = result.actual;

        if (!result.pass) {
            this.logSingleFailure(result);
        }

        const passCount = this.results.filter((item) => item.pass).length;
        const statusWord = result.pass ? 'PASS' : 'FAIL';
        this.statusTarget.textContent = `${passCount}/${this.results.length} cases passing. Selected: ${result.name} (${statusWord}).`;
    }

    escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }
}
