<?php
// Library ----------------------------------
require __DIR__ . '/lib/Utils.php';
require __DIR__ . '/lib/Router.php';
require __DIR__ . '/lib/Request.php';
require __DIR__ . '/lib/Database.php';
require __DIR__ . '/lib/DatabaseModel.php';
require __DIR__ . '/lib/Auth.php';
// Models -----------------------------------
require __DIR__ . '/models/UserModel.php';
require __DIR__ . '/models/OrderModel.php';
require __DIR__ . '/models/WorkshiftModel.php';

// Check database connection
if(!Database::connect()) {
    Request::finishErrorServer('Ошибка подключения к БД');
    return; 
}

// Base API
$api = new Router();
$api->set404(function() {
    header('HTTP/1.1 404 Not Found');
    Request::finishError('Страница(endpoint) не найдена', 400);
});

// Auth -------------------------------------
$api->post('api/sign-in', function() {
    Request::begin();

    // Check fields
    if(Request::requireFields(['username','password'])) {
        $fields = Request::getFields();

        // Get user
        $user = DatabaseModel::select(UserModel::getInstance(), array(
            'username' => $fields['username']
        ));

        // Check credentials
        if($fields['password'] == $user['password']) {
            $headers = array('alg' =>'HS256','typ'=>'JWT');
            $payload = array('role'=>$user['role']);

            // (no token expiration)
            $jwt = Auth::generateJwt($headers, $payload);

            // Return JWT
            Request::finishSuccessData(array(
                'jwt' => $jwt,
                'role' => $user['role']
            ));
        }
        else {
            Request::finishError('Пороли не совпадают или такого аккаунта не существует', 400);
        }
    }
});

// (Admin/Waiter/Cook) Get role of account
$api->get('api/role', function() {
    /*
    * Get role of current account
    * For(role): *
    */
    Request::begin();

    // Get role
    $role = Request::getUserRole();

    // Check
    if(isset($role)) {
        Request::finishSuccessData(array(
            'role' => $role
        ));
    }
});

// Admin --------------------------------
$api->put('api/user', function() {
    /*
    * Add user to the system
    * For(role): admin
    */

    Request::begin();

    if(Request::requireFields(['username', 'phone', 'password', 'role'])) { 
        if(Request::requireRole(['admin'])) {
            $fields = Request::getFields();

            // Create user
            $result = DatabaseModel::insert(UserModel::getInstance(), array(
                'username' => $fields['username'],
                'phone' => $fields['phone'],
                'password' => $fields['password'],
                'role' => $fields['role']
            ));
            $user = DatabaseModel::selectLast(UserModel::getInstance());
    
            // Finish
            Request::finishSuccess(array(
                'success' => 'true',
                'user' => $user
            ));
        }
    }
});

$api->get('api/workshifts', function() {
    Request::begin();

    if(Request::requireRole(['admin'])) {
        $fields = Request::getFields();

        // Get workshift by id
        if(isset($fields['id'])) {
            //Get workshift
            $workshift = DatabaseModel::select(WorkshiftModel::getInstance(), array(
                'id' => intval($fields['id'])
            ));

            //Found workshift
            if(isset($workshift) && $workshift) {
                // Get orders
                $orders = DatabaseModel::select(OrderModel::getInstance(), array(
                    'workshift_id' => intval($fields['id'])
                ), true);

                // Finish
                Request::finishSuccessData(array(
                    'workshift' => $workshift,
                    'orders' => $orders
                ));
            }
            else {
                Request::finishError('Смена не найдена', 404);
            }
        }
        // Get list of workshifts
        else {
            $workshifts = DatabaseModel::selectAll(WorkshiftModel::getInstance());

            Request::finishSuccessData($workshifts);
        }
    }
});
$api->put('api/workshifts', function() {
    /*
    * Create new workshift (with today date)
    * For(role): admin
    */

    Request::begin();

    if(Request::requireRole(['admin'])) {
        $date = date('Y-m-d H:i:s');

        // Create workshift
        $result = DatabaseModel::insert(WorkshiftModel::getInstance(), array(
            'date' => $date,
            'workers' => 'JSON_ARRAY()'
        ));
        $workshift = DatabaseModel::selectLast(WorkshiftModel::getInstance());

        // Finish
        Request::finishSuccess(array(
            'success' => 'true',
            'workshift' => array(
                'id' => $workshift['id'],
                'date' => $workshift['date']
            )
        ));
    }
});
$api->post('api/workshifts/assign', function() {
    /*
    * Add user to the workshift (Today workshift)
    * For(role): admin
    */

    Request::begin();

    if(Request::requireFields(['workshift_id', 'user_id'])) { 
        if(Request::requireRole(['admin'])) {
            $fields = Request::getFields();

            // Append to workshift
            $result = DatabaseModel::arrayAppend(WorkshiftModel::getInstance(),
                'workers',
                $fields['user_id'],
                array(
                    'id' => intval($fields['workshift_id'])
                )
            );

            // Finish
            Request::finishSuccess(array(
                'success' => 'true'
            ));
        }
    }
});

// Waiter --------------------------------
$api->get('api/orders', function() {
    Request::begin();

    if(Request::requireRole(['admin','waiter'])) {
        $fields = Request::getFields();

        // Get order by id
        if(isset($fields['id'])) {
            //Get order
            $order = DatabaseModel::select(OrderModel::getInstance(), array(
                'id' => intval($fields['id'])
            ));

            //Found order
            if(isset($order) && $order) {
                // Finish
                Request::finishSuccessData($order);
            }
            else {
                Request::finishError('Заказ не найден', 404);
            }
        }
        // Get list of orders
        else {
            $orders = DatabaseModel::selectAll(OrderModel::getInstance());

            Request::finishSuccessData($orders);
        }
    }
});
$api->put('api/orders', function() {
    /*
    * Add order to the system
    * For(role): waiter
    */

    Request::begin();

    if(Request::requireFields(['workshift_id', 'products'])) { 
        if(Request::requireRole(['admin', 'waiter'])) {
            $fields = Request::getFields();

            // Create user
            $result = DatabaseModel::insert(OrderModel::getInstance(), array(
                'workshift_id' => $fields['workshift_id'],
                'products' => $fields['products'],
                'status' => 'created',
            ));
            $order = DatabaseModel::selectLast(OrderModel::getInstance());
    
            // Finish
            Request::finishSuccess(array(
                'success' => 'true',
                'order' => $order
            ));
        }
    }
});
$api->patch('api/orders', function() {
    /*
    * Change order products
    * For(role): waiter
    */

    Request::begin();

    if(Request::requireFields(['id', 'products'])) { 
        if(Request::requireRole(['admin', 'waiter'])) {
            $fields = Request::getFields();

            // Update order
            $result = DatabaseModel::update(OrderModel::getInstance(), 
                array(
                    'products' => $fields['products'],
                ),
                array(
                    'id' => intval($fields['id']),
                )
            );

            // Finish
            Request::finishSuccess(array(
                'success' => 'true'
            ));
        }
    }
});

// Cook --------------------------------

// Waiter & Cook --------------------------------
$api->patch('api/orders/status', function() {
    /*
    * Change order status
    * For(role): waiter/cook
    */

    Request::begin();

    if(Request::requireFields(['id', 'status'])) { 
        if(Request::requireRole(['admin', 'waiter', 'cook'])) {
            $fields = Request::getFields();

            $status = $fields['status'];
            if($status == 'cooking' || $status == 'done' || $status == 'paid' || $status == 'cancelled') {
                // Update order
                $result = DatabaseModel::update(OrderModel::getInstance(), 
                    array(
                        'status' => $status,
                    ),
                    array(
                        'id' => intval($fields['id']),
                    )
                );

                // Finish
                Request::finishSuccess(array(
                    'success' => 'true'
                ));
            }
            else {
                Request::finishErrorValidation(array(
                    'status' => 'Поле должно иметь одно из данных значений: cooking, done, paid, cancelled'
                ));
            }
        }
    }
});
$api->run();