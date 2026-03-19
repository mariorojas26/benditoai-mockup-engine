<?php
if (!defined('ABSPATH')) exit;

/**
 * SUBMENÚ: Usuarios & Planes
 */
add_action('admin_menu', function(){

    add_submenu_page(
        'bendidoai-dashboard', // slug padre (tu menú principal)
        'Usuarios & Planes 1',
        'Administrar usuarios y Planes',
        'manage_options',
        'benditoai-users-plans',
        'benditoai_render_users_plans_page'
    );

});

/**
 * RENDER DE LA PÁGINA
 */
function benditoai_render_users_plans_page(){

    if(!current_user_can('manage_options')) return;

    $users = get_users();

    ?>

    <div class="wrap">
        <h1>Usuarios & Planes</h1>

        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Email</th>
                    <th>Plan</th>
                    <th>Acción</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach($users as $user): 
                    
                    $plan = get_user_meta($user->ID, 'benditoai_plan', true);
                    if(!$plan) $plan = 'starter';

                ?>

                <tr>

                    <td><?php echo $user->ID; ?></td>
                    <td><?php echo esc_html($user->user_login); ?></td>
                    <td><?php echo esc_html($user->user_email); ?></td>

                    <td>
                        <select class="benditoai-plan-select" data-user="<?php echo $user->ID; ?>">
                            <option value="starter" <?php selected($plan,'starter'); ?>>starter</option>
                            <option value="pro" <?php selected($plan,'pro'); ?>>pro</option>
                            <option value="elite" <?php selected($plan,'elite'); ?>>elite</option>
                        </select>
                    </td>

                    <td>
                        <button 
                            class="button button-primary benditoai-save-plan"
                            data-user="<?php echo $user->ID; ?>"
                        >
                            Guardar
                        </button>
                    </td>

                </tr>

                <?php endforeach; ?>

            </tbody>
        </table>

    </div>

    <script>
    document.addEventListener("click", function(e){

        if(!e.target.classList.contains("benditoai-save-plan")) return;

        const btn = e.target;
        const userId = btn.dataset.user;

        const select = document.querySelector(`.benditoai-plan-select[data-user="${userId}"]`);
        const plan = select.value;

        btn.disabled = true;
        btn.innerText = "Guardando...";

        fetch(ajaxurl,{
            method:"POST",
            headers:{
                "Content-Type":"application/x-www-form-urlencoded"
            },
            body:`action=benditoai_update_user_plan&user_id=${userId}&plan=${plan}`
        })
        .then(res=>res.json())
        .then(res=>{

            if(res.success){
                btn.innerText = "Guardado ✅";
            }else{
                alert(res.data.message || "Error");
                btn.innerText = "Guardar";
                btn.disabled = false;
            }

        });

    });
    </script>

    <?php
}