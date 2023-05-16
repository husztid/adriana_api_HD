<?php

class CustomerController{

    public function __construct( private CustomerGateway $gateway ){

    }

    public function processRequest(string $method, ?string $id): void{
        if ( $id ){
            $this-> process_resource_request($method, $id);
        }else{
            $this-> process_collection_request($method);
        }
    }

    private function process_resource_request(string $method, string $id): void{
        $customer = $this->gateway->get($id);

        if ( !$customer ){
            http_response_code(404);
            echo json_encode( ["message" => "Nincs ilyen partner!"] );
            return;
        }

        switch( $method ){
            case "GET":
                echo json_encode($customer);
                break;

            case "PATCH":

                $data = (array) json_decode(file_get_contents("php://input"), true);
                $data['is_patch'] = true;
                
                $errors = $this->get_valid_errs($data);

                if( !empty($errors) ) {
                    http_response_code(422);
                    echo json_encode( ["errors" => $errors] );
                    break;
                }
                
                $rows = $this->gateway->update($customer, $data);

                echo json_encode([
                    "message" => "Partner módosítva (id: $id)!",
                    "rows" => $rows
                ]);

                break;

            case "DELETE":
                $rows = $this->gateway->delete($id);

                echo json_encode([
                    "message" => "Vevő törölve (" . $customer['name']. ")!",
                    "rows" => $rows
                ]);

                break;

            default:
                http_response_code(405);
                header("Allow: GET, PATCH, DELETE");

        }
        
    }

    private function process_collection_request(string $method): void{
        switch( $method ){
            case "GET":
                //$szukseges_oszlopok = array("id", "name");
                $szukseges_oszlopok = array();
                echo json_encode( $this->gateway->getAll($szukseges_oszlopok) );
                break;

            case "POST":
                $data = (array) json_decode(file_get_contents("php://input"), true);

                $errors = $this->get_valid_errs($data);

                if( !empty($errors) ) {
                    http_response_code(422);
                    echo json_encode( ["errors" => $errors] );
                    break;
                }
                $lastID = $this->gateway->create($data);

                http_response_code(201);

                echo json_encode([
                    "message" => "Partner rögzítve!",
                    "id" => $lastID
                ]);

                break;

            default:
                http_response_code(405);
                header("Allow: GET, POST");
        }
    }

    private function get_valid_errs(array $data): array{
        
        $errors = array();

        # ha patchelünk/updatelünk nem biztos, hogy minden változót megadunk, így arra ellenőrzés sem kell
        $is_patch = ( isset($data['is_patch']) && $data['is_patch'] );

        if ( empty($data['name']) ){

            $name_hiba_mehet = true;
            if ( $is_patch && !isset($data['name']) ){
                $name_hiba_mehet = false;
            }

            if ( $name_hiba_mehet ){
                $errors[] = "Név megadása kötelező!";
            }
        }

        if ( empty($data['cim']) ){

            $cim_hiba_mehet = true;
            if ( $is_patch && !isset($data['cim']) ){
                $cim_hiba_mehet = false;
            }

            if ( $cim_hiba_mehet ){
                $errors[] = "Cím megadása kötelező!";
            }
        }

        return $errors;
    }
}