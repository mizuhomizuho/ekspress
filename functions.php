<?php

class Test {
    
    function ajaxTestjobcart(): void {
        
        $result = [];

        if (
            !isset(
                $_POST['nonce'],
                $_POST['template'],
                $_POST['time']
            )
        ) {
            $result['status'] = 'empty';
        }
        else {
            $userId = get_current_user_id();

            if(!wp_verify_nonce((string) $_POST['nonce'], $userId)){
                $result['status'] = false;
            }
            else {
                update_user_meta($userId, 'template', esc_sql((string) $_POST['template']));

                if (get_user_meta($userId, 'template-date')) {
                    update_user_meta($userId, 'template-date-update', (int) $_POST['time']);
                    $result['status'] = 'update';
                }
                else {
                    add_user_meta($userId, 'template-date', (int) $_POST['time']);
                    $result['status'] = 'inserted';
                }
            }
        }
        
        echo json_encode($result);
        
        wp_die();
    }
    
    private function addAjax(): void {
        
        if (!wp_doing_ajax()) {
            return;
        }
        
        add_action('wp_ajax_test_jobcart', [$this, 'ajaxTestjobcart'], 1);
        add_action('wp_ajax_nopriv_test_jobcart', [$this, 'ajaxTestjobcart'], 1);
    }
    
    private function addScript(): void {
        add_action('wp_enqueue_scripts', function() {
            $settings = [];
            $settings['url'] = admin_url('admin-ajax.php');
            $settings['nonce'] = wp_create_nonce(get_current_user_id());
            $settings['templates'] = ['green', 'yellow'];

            wp_enqueue_script('customscripts', get_template_directory_uri() . '/js/customscripts.js');
            wp_localize_script('customscripts', 'settings', $settings);
        }); 
    }
    
    function init(): void {
        $this->addScript();
        $this->addAjax();
    }
}

(new Test())->init();



class Test2 {
    
    private function normCategories(array $arr): array {
        
        $res = [];
        
        foreach ($arr as $arrKey => $arrValue) {
            
            if ($arrKey) {
                
                $key = $arr[$arrKey-1];
                unset($arr[$arrKey-1]);
                
                $arrNew = [];
                foreach ($arr as $arrVal) {
                    $arrNew[] = $arrVal;
                }
                
                $fn = __FUNCTION__;
                $res[$key]['ch'] = $this->$fn($arrNew);
                
                break;
            }
            else {
                $res[$arrValue] = [
                    'name' => $arrValue,
                ];
            }
        }
        
        return $res;
    }
    
    private function addTerms(array $arr, int $parentId = 0): void {
        
        foreach ($arr as $arrVal) {
            
            $params = [];

            if ($parentId) {
                $params['parent'] = $parentId;
            }

            $insertRes = wp_insert_term(
                $arrVal['name'],
                'job_listing_categories',
                $params
            );

            if(!is_wp_error($insertRes)) {
                
                $termId = $insertRes['term_id'];
                
                if (isset($arrVal['ch'])) {
                    
                    $fn = __FUNCTION__;
                    $this->$fn($arrVal['ch'], $termId);
                }
            }
        }
    }
    
    private function addTermItems(): void {
    
        add_action('init', function () {
        
            $listingCategories = [
                'главная2',
                'главная',
                'главная->дочерняя',
                'главная->дочерняя2',
                'главная->дочерняя2->дочерняя3',
                'главная->дочерняя2->дочерняя4',
            ];

            $normCategories = [];

            foreach ($listingCategories as $listingCategoriesValue) {
                $arr = explode('->', $listingCategoriesValue);
                $normCategories = array_replace_recursive($normCategories, $this->normCategories($arr));
            }

            $this->addTerms($normCategories);
        });
    }
    
    private function register(): void {
    
        add_action('init', function () {
    
            register_post_type( 
                'job_listing',
                [
                    'labels' => [
                        'menu_name' => 'Rudos',
                    ],
                    'public' => true,
                ]
            );

            register_taxonomy (
                'job_listing_categories',
                ['job_listing'],
                [
                    'label' => 'Rudos - категории',
                    'hierarchical' => true
                ]
            );
        });
    }
    
    function init(): void {
        $this->register();
        $this->addTermItems();
    }
}

(new Test2())->init();



