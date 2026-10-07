import ApiTreeController from '../../vendor/survos/tree-bundle/assets/src/controllers/api_tree_controller.js';
import { getTree } from '../../vendor/survos/tree-bundle/assets/src/jstree_runtime.js';

// Inventory policy belongs to the application; the shared tree stays generic.
export default class extends ApiTreeController {
    async renderTree() {
        await super.renderTree();
        const tree = getTree(this.ajaxTarget);
        const isRoot = node => node?.parent === '#';
        tree.settings.core.check_callback = (operation, node, parent) => {
            if (['rename_node', 'delete_node', 'move_node'].includes(operation) && isRoot(node)) {
                return false;
            }
            if (['create_node', 'move_node'].includes(operation) && (!parent || parent.id === '#')) {
                return false;
            }
            return true;
        };
        const items = tree.settings.contextmenu.items;
        tree.settings.contextmenu.items = node => {
            const menu = items(node);
            if (isRoot(node)) {
                delete menu.rename;
                delete menu.remove;
            }
            return menu;
        };
    }

    async persistCreate(detail) { return this.withRecovery(() => super.persistCreate(detail)); }
    async persistRename(detail) { return this.withRecovery(() => super.persistRename(detail)); }
    async persistMove(detail) { return this.withRecovery(() => super.persistMove(detail)); }
    async persistDelete(detail) { return this.withRecovery(() => super.persistDelete(detail)); }

    async withRecovery(write) {
        try {
            return await write();
        } catch (error) {
            // Restore the server's tree after a rejected optimistic edit.
            await this.renderTree();
            throw error;
        }
    }
}
