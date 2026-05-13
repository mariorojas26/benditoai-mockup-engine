<?php
if (!defined('ABSPATH')) exit;

/**
 * Submenu: Usuarios & Planes
 */
add_action('admin_menu', function(){

    add_submenu_page(
        'bendidoai-dashboard',
        'Usuarios & Planes',
        'Administrar usuarios y Planes',
        'manage_options',
        'benditoai-users-plans',
        'benditoai_render_users_plans_page'
    );

});

/**
 * Render de la pagina.
 */
function benditoai_render_users_plans_page(){

    if(!current_user_can('manage_options')) return;

    $users = get_users();
    $plans = function_exists('benditoai_get_plans') ? benditoai_get_plans() : [];

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
                    <th>Limites</th>
                    <th>Accion</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach($users as $user):
                    $plan = function_exists('benditoai_get_user_plan_key')
                        ? benditoai_get_user_plan_key($user->ID)
                        : 'starter';
                    $plan_data = isset($plans[$plan]) ? $plans[$plan] : ($plans['starter'] ?? []);
                ?>

                <tr>

                    <td><?php echo esc_html($user->ID); ?></td>
                    <td><?php echo esc_html($user->user_login); ?></td>
                    <td><?php echo esc_html($user->user_email); ?></td>

                    <td>
                        <select class="benditoai-plan-select" data-user="<?php echo esc_attr($user->ID); ?>">
                            <?php foreach($plans as $plan_key => $config): ?>
                                <option value="<?php echo esc_attr($plan_key); ?>" <?php selected($plan, $plan_key); ?>>
                                    <?php echo esc_html($config['name'] ?? ucfirst($plan_key)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>

                    <td class="benditoai-plan-limits" data-user="<?php echo esc_attr($user->ID); ?>">
                        <?php
                        printf(
                            '%s tokens / %s modelos / %s outfits',
                            esc_html($plan_data['tokens'] ?? 0),
                            esc_html($plan_data['max_modelos'] ?? 0),
                            esc_html($plan_data['max_outfits'] ?? 0)
                        );
                        ?>
                    </td>

                    <td>
                        <button
                            class="button button-primary benditoai-save-plan"
                            data-user="<?php echo esc_attr($user->ID); ?>"
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
        const limits = document.querySelector(`.benditoai-plan-limits[data-user="${userId}"]`);
        const plan = select.value;

        btn.disabled = true;
        btn.innerText = "Guardando...";

        fetch(ajaxurl,{
            method:"POST",
            headers:{
                "Content-Type":"application/x-www-form-urlencoded"
            },
            body:new URLSearchParams({
                action:"benditoai_update_user_plan",
                user_id:userId,
                plan:plan
            })
        })
        .then(res=>res.json())
        .then(res=>{

            if(res.success){
                btn.innerText = "Guardado";
                if(limits && res.data && res.data.limits){
                    limits.innerText = `${res.data.limits.tokens} tokens / ${res.data.limits.max_modelos} modelos / ${res.data.limits.max_outfits} outfits`;
                }
                setTimeout(()=>{
                    btn.innerText = "Guardar";
                    btn.disabled = false;
                }, 1200);
            }else{
                alert((res.data && res.data.message) || "Error");
                btn.innerText = "Guardar";
                btn.disabled = false;
            }

        })
        .catch(()=>{
            alert("Error actualizando el plan");
            btn.innerText = "Guardar";
            btn.disabled = false;
        });

    });
    </script>

    <?php
}
