<?php
/**
 * Plugin Name: Custom Post Manager
 * Description: Ajoute une interface pour gérer les onglets du menu admin.
 * Version: 1.0
 * Author: Eric
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class AdminMenuManager {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_plugin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_menu', [$this, 'generate_custom_menus']);
    }

    public function add_plugin_menu() {
        add_menu_page(
            'Gestion Menu Admin',
            'Menu Admin',
            'manage_options',
            'admin-menu-manager',
            [$this, 'admin_page'],
            'dashicons-menu',
            99
        );
    }

    public function register_settings() {
        register_setting('admin_menu_manager_options', 'admin_menu_items');
    }

    public function admin_page() {
        $menu_items = get_option('admin_menu_items', []);
        ?>
<div class="wrap">
    <h1>Gestion des Onglets du Menu Admin</h1>
    <form method="post" action="options.php">
        <?php settings_fields('admin_menu_manager_options'); ?>
        <table class="form-table" id="menu-items">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Slug</th>
                    <th>Icône (dashicons)</th>
                    <th>Supprimer</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($menu_items as $index => $item) : ?>
                <tr>
                    <td><input type="text" name="admin_menu_items[<?php echo $index; ?>][name]"
                            value="<?php echo esc_attr($item['name']); ?>" required></td>
                    <td><input type="text" name="admin_menu_items[<?php echo $index; ?>][slug]"
                            value="<?php echo esc_attr($item['slug']); ?>" required></td>
                    <td><input type="text" name="admin_menu_items[<?php echo $index; ?>][icon]"
                            value="<?php echo esc_attr($item['icon']); ?>"></td>
                    <td><button type="button" class="remove-row">X</button></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button type="button" id="add-row">Ajouter un onglet</button>
        <?php submit_button(); ?>
    </form>
</div>
<script>
document.getElementById('add-row').addEventListener('click', function() {
    let table = document.getElementById('menu-items').querySelector('tbody');
    let index = table.children.length;
    let row = document.createElement('tr');
    row.innerHTML = `
                    <td><input type="text" name="admin_menu_items[\${index}][name]" required></td>
                    <td><input type="text" name="admin_menu_items[\${index}][slug]" required></td>
                    <td><input type="text" name="admin_menu_items[\${index}][icon]"></td>
                    <td><button type="button" class="remove-row">X</button></td>
                `;
    table.appendChild(row);
    row.querySelector('.remove-row').addEventListener('click', function() {
        row.remove();
    });
});

document.querySelectorAll('.remove-row').forEach(button => {
    button.addEventListener('click', function() {
        this.closest('tr').remove();
    });
});
</script>
<?php
    }

    public function generate_custom_menus() {
        $menu_items = get_option('admin_menu_items', []);
        foreach ($menu_items as $item) {
            if (!empty($item['name']) && !empty($item['slug'])) {
                add_menu_page(
                    esc_html($item['name']),
                    esc_html($item['name']),
                    'manage_options',
                    esc_attr($item['slug']),
                    function () use ($item) {
                        echo '<div class="wrap"><h1>' . esc_html($item['name']) . '</h1><p>Page personnalisée.</p></div>';
                    },
                    $item['icon'] ?? 'dashicons-admin-generic',
                    100
                );
            }
        }
    }
}

new AdminMenuManager();