<?php

/**
 * Plugin Name: Admin Menu Manager
 * Description: Gérer les menus du menu principal administrateur.
 * Version: 1.1.0
 * Author: Eric
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class AdminMenuManager
{
    private $optionName = 'admin_menu_manager_items';


    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu_page'));
        add_action('admin_init', array($this, 'handle_form_submission'));
        add_action('admin_init', array($this, 'check_menu_activation')); // Vérification dans l’admin
        add_action('wp_loaded', array($this, 'check_menu_activation')); // Vérification sur le site


    }

    public function add_admin_menu_page()
    {
        add_menu_page(
            'Gestion du Menu Principal',
            'Ajout module',
            'manage_options',
            'admin-menu-manager',
            array($this, 'render_admin_page'),
            'dashicons-menu'
        );

        add_submenu_page(
            'admin-menu-manager',
            'Menu Principal',
            'Menu Principal',
            'manage_options',
            'admin-menu-manager-main',
            array($this, 'render_admin_page')
        );

        $menus = get_option($this->optionName, []);
        foreach ($menus as $menu) {
            if (!empty($menu['parent']) && $menu['active']) {
                add_submenu_page($menu['parent'], $menu['title'], $menu['title'], 'manage_options', $menu['slug']);
            } elseif ($menu['active']) {
                add_menu_page($menu['title'], $menu['title'], 'manage_options', $menu['slug'], '', 'dashicons-admin-generic');
            }
        }
    }

    public function render_admin_page()
    {
        date_default_timezone_set('Europe/Paris'); // Définir le fuseau horaire par défaut
        $menus = get_option($this->optionName, []);
        $editIndex = isset($_GET['edit']) ? intval($_GET['edit']) : -1;
?>
        <div class="wrap">
            <h1>Gestion du Menu Principal</h1>
            <form method="post">
                <?php wp_nonce_field('admin_menu_manager_action', 'admin_menu_manager_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="menu_title">Nom du menu</label></th>
                        <td><input type="text" name="menu_title" id="menu_title" required></td>
                    </tr>
                    <tr>
                        <th><label for="menu_slug">Slug du menu</label></th>
                        <td><input type="text" name="menu_slug" id="menu_slug" required></td>
                    </tr>
                    <tr>
                        <th><label for="menu_parent">Parent</label></th>
                        <td>
                            <select name="menu_parent" id="menu_parent">
                                <option value="">Aucun</option>
                                <?php foreach ($menus as $menu) { ?>
                                    <option value="<?php echo esc_attr($menu['slug']); ?>"><?php echo esc_html($menu['title']); ?></option>
                                <?php } ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="menu_active">Activer immédiatement</label></th>
                        <td><input type="checkbox" name="menu_active" id="menu_active" value="1"></td>
                    </tr>
                    <tr>
                        <th><label for="menu_activation_date">Activer à une date spécifique</label></th>
                        <td><input type="datetime-local" name="menu_activation_date" id="menu_activation_date"></td>
                    </tr>
                </table>
                <input type="submit" name="add_menu" value="Ajouter le menu" class="button button-primary">
            </form>

            <h2>Menus existants</h2>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Slug</th>
                        <th>Parent</th>
                        <th>Actif</th>
                        <th>Date d'activation</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($menus as $index => $menu) { ?>
                        <tr>
                            <?php if ($editIndex === $index) { ?>
                                <form method="post">
                                    <?php wp_nonce_field('admin_menu_manager_action', 'admin_menu_manager_nonce'); ?>
                                    <input type="hidden" name="edit_index" value="<?php echo $index; ?>">
                                    <td><input type="text" name="menu_title" value="<?php echo esc_attr($menu['title']); ?>" required></td>
                                    <td><input type="text" name="menu_slug" value="<?php echo esc_attr($menu['slug']); ?>" required></td>
                                    <td>
                                        <select name="menu_parent">
                                            <option value="">Aucun</option>
                                            <?php foreach ($menus as $m) { ?>
                                                <option value="<?php echo esc_attr($m['slug']); ?>" <?php selected($menu['parent'], $m['slug']); ?>>
                                                    <?php echo esc_html($m['title']); ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </td>
                                    <td><input type="checkbox" name="menu_active" value="1" <?php checked($menu['active'], 1); ?>></td>
                                    <td><input type="datetime-local" name="menu_activation_date" value="<?php echo esc_attr(date('Y-m-d H:i', $menu['activation_date'])); ?>"></td>
                                    <td>
                                        <input type="submit" name="save_menu" value="Enregistrer" class="button button-primary">
                                        <a href="<?php echo admin_url('admin.php?page=admin-menu-manager-main'); ?>" class="button">Annuler</a>
                                    </td>
                                </form>
                            <?php } else { ?>
                                <td><?php echo esc_html($menu['title']); ?></td>
                                <td><?php echo esc_html($menu['slug']); ?></td>
                                <td><?php echo esc_html($menu['parent'] ?: 'Aucun'); ?></td>
                                <td><?php echo $menu['active'] ? 'Oui' : 'Non'; ?></td>
                                <td><?php echo !empty($menu['activation_date']) ? esc_html(date('d-m-Y H:i', $menu['activation_date'])) : 'Immédiat'; ?></td>
                                <td>
                                    <a href="<?php echo admin_url('admin.php?page=admin-menu-manager-main&edit=' . $index); ?>" class="button">Modifier</a>
                                    <form method="post" style="display:inline;">
                                        <?php wp_nonce_field('admin_menu_manager_action', 'admin_menu_manager_nonce'); ?>
                                        <input type="hidden" name="delete_menu" value="<?php echo $index; ?>">
                                        <input type="submit" value="Supprimer" class="button button-danger">
                                    </form>
                                </td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
<?php
    }

    public function handle_form_submission()
    {
        date_default_timezone_set('Europe/Paris'); // Définir le fuseau horaire par défaut
        if (isset($_POST['add_menu']) || isset($_POST['save_menu'])) {
            check_admin_referer('admin_menu_manager_action', 'admin_menu_manager_nonce');
            $menus = get_option($this->optionName, []);
            $menu_data = [
                'title' => sanitize_text_field($_POST['menu_title']),
                'slug' => sanitize_title($_POST['menu_slug']),
                'parent' => sanitize_text_field($_POST['menu_parent']),
                'active' => isset($_POST['menu_active']) ? 1 : 0,
                'activation_date' => sanitize_text_field(strtotime($_POST['menu_activation_date'])),

            ];
            if (isset($_POST['save_menu'])) {
                $menus[intval($_POST['edit_index'])] = $menu_data;
            } else {
                $menus[] = $menu_data;
            }
            update_option($this->optionName, $menus);
            wp_redirect(admin_url('admin.php?page=admin-menu-manager-main'));
            exit;
        }

        if (isset($_POST['delete_menu'])) {
            check_admin_referer('admin_menu_manager_action', 'admin_menu_manager_nonce');
            $menus = get_option($this->optionName, []);
            unset($menus[intval($_POST['delete_menu'])]);
            update_option($this->optionName, array_values($menus));
            wp_redirect(admin_url('admin.php?page=admin-menu-manager-main'));
            exit;
        }
    }


    public function check_menu_activation()
    {
        
        date_default_timezone_set('Europe/Paris'); // Définir le fuseau horaire par défaut
        $menus = get_option($this->optionName, []);

        if (empty($menus)) {
            return; // Aucun menu à vérifier
        }

        $updated = false;
        foreach ($menus as $index => $menu) {
            $current_time = time();           

            if (!empty($menu['activation_date'])) {
                // Vérifier si la date d'activation est atteinte
                // et mettre à jour le statut actif du menu
                $activation_time = $menu['activation_date'];
                if ($activation_time <= $current_time && $menu['active'] === 0) {
                    $menus[$index]['active'] = 1; // Activer le menu
                    $updated = true;
                } elseif ($activation_time > $current_time && $menu['active'] === 1) {
                    $menus[$index]['active'] = 0; // Désactiver le menu
                    $updated = true;
                }
            }
        }
        if ($updated) {
            update_option($this->optionName, $menus);
        }
    }
}

$adminMenuManager = new AdminMenuManager();
