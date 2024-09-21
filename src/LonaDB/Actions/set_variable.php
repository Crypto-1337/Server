<?php

return new class {
    public function run($LonaDB, $data, $client) : void {
        if (empty($data['table']['name'])) {
            $response = json_encode(["success" => false, "err" => "bad_table_name", "process" => $data['process']]);
            socket_write($client, $response);
            socket_close($client);
            return;
        }

        $tableName = $data['table']['name'];

        if (!$LonaDB->TableManager->GetTable($tableName)) {
            $response = json_encode(["success" => false, "err" => "table_missing", "process" => $data['process']]);
            socket_write($client, $response);
            socket_close($client);
            return;
        }

        $table = $LonaDB->TableManager->GetTable($tableName);

        if (!$table->CheckPermission($data['login']['name'], "write")) {
            $response = json_encode(["success" => false, "err" => "missing_permissions", "process" => $data['process']]);
            socket_write($client, $response);
            socket_close($client);
            return;
        }

        if (empty($data['variable']['name'])) {
            $response = json_encode(["success" => false, "err" => "bad_variable_name", "process" => $data['process']]);
            socket_write($client, $response);
            socket_close($client);
            return;
        }

        if (empty($data['variable']['value'])) {   
            $response = json_encode(["success" => false, "err" => "bad_variable_value", "process" => $data['process']]);
            socket_write($client, $response);
            socket_close($client);
            return;
        }

        $variableName = $data['variable']['name'];
        $variableValue = $data['variable']['value'];

        $table->Set($variableName, $variableValue, $data['login']['name']);

        $response = json_encode(["success" => true, "process" => $data['process']]);
        socket_write($client, $response);
        socket_close($client);

        //Run plugin event
        $LonaDB->PluginManager->RunEvent($data['login']['name'], "valueSet", [ "name" => $data['variable']['name'], "value" => $data['variable']['value'] ]);
    }
};
