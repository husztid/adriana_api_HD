<?php

class CustomerGateway{
    private PDO $conn;

    # db connect
    public function __construct(Database $database){
        $this->conn = $database->get_conn();
    }

    # lekér minden rekordot az adott adattáblából
    public function getAll( array $szukseges_oszlopok = null ): array{

        # extra változóban megadhatjuk a lekérni kívánt oszlopokat (optimalizáljuk a lekérdezést, hogy ne haljon be a rendszer)
        $collumns = "*";
        if( !empty($szukseges_oszlopok) ){
            $collumns = implode(",", $szukseges_oszlopok);
        }
        
        $sql = "SELECT $collumns 
                FROM customers";

        $stmt = $this->conn->query($sql);

        $data = [];
        while( $row = $stmt->fetch(PDO::FETCH_ASSOC) ){

            $data[] = $row;
        }

        return $data;
    }

    # létrehoz egy sort a megadott adatokkal (előtte majd fut ellenőrzés is)
    public function create(array $data): string{

        # előkészítjük a lekérdezésünket
        $sql = "INSERT INTO customers (name, cim, ugyfel_kod, szerzodes_datuma)
                    VALUES (:name, :cim, :ugyfel_kod, :szerzodes_datuma)";

        $stmt = $this->conn->prepare($sql);

        # definiáljuk az oszlopokat
        $stmt->bindValue(":name", $data['name'], PDO::PARAM_STR);
        $stmt->bindValue(":cim", $data['cim'], PDO::PARAM_STR);
        $stmt->bindValue(":ugyfel_kod", $data['ugyfel_kod'] ?? "", PDO::PARAM_STR);
        $stmt->bindValue(":szerzodes_datuma", $data['szerzodes_datuma'] ?? null, PDO::PARAM_STR);

        $stmt->execute();

        return $this->conn->lastInsertId();
    }
   
    # ez lesz a get függvényünk, ezzel kérjük le az adatbázisból a rekordokat
    public function get(string $id): array | false{
        $sql = "SELECT *
                FROM customers
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data;
    }

    # ezzel a függvénnyel meglévő rekordokat módosítunk
    public function update( array $current, array $new): int{

        $sql = "UPDATE customers
                SET name = :name, cim = :cim, ugyfel_kod = :ugyfel_kod, szerzodes_datuma = :szerzodes_datuma
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        # definiálunk, amit nem módosítottak, az marad a régi
        $stmt->bindValue(":name", $new['name'] ?? $current['name'], PDO::PARAM_STR );
        $stmt->bindValue(":cim", $new['cim'] ?? $current['cim'], PDO::PARAM_STR );
        $stmt->bindValue(":ugyfel_kod", $new['ugyfel_kod'] ?? $current['ugyfel_kod'], PDO::PARAM_STR );
        $stmt->bindValue(":szerzodes_datuma", $new['szerzodes_datuma'] ?? $current['szerzodes_datuma'], PDO::PARAM_STR );
        $stmt->bindValue(":id", $current['id'], PDO::PARAM_INT );

        $stmt->execute();

        return $stmt->rowCount();
    }

    # fv. törléshez
    public function delete(string $id): int{

        $sql = "DELETE 
                FROM customers
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue( ":id", $id, PDO::PARAM_INT );
        $stmt->execute();

        return $stmt->rowCount();
    }
}