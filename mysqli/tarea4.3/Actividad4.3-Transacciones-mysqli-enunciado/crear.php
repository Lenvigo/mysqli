<!DOCTYPE html>
<html lang="es" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.min.js" integrity="sha384-QJHtvGhmr9XOIpI6YVutG+2QOK9T+ZnN4kzFN1RtK3zEFEIsxhlmWl5/YESvpZ13" crossorigin="anonymous"></script>

    <title>Crear libro</title>
</head>

<body>
    <?php
    require_once 'conexion.php';
    require_once 'util.php';

    $pdate = null;
    $isbn = null;
    $pub_Id = null;
    //todos los autores disponibles en BD
    $authors = null;
    //los ids de los autores del libro
    $book_author_ids = null;
    $title = "";
    $exito = true;


    try {
        $publishers = findAllPublishers();
        $authors = findAllAuthors();

        if (isset($_POST["title"])) {
            if (isNotEmpty($_POST["title"])) {
                $title = $_POST["title"];
            }

            if (isset($_POST["isbn"]) &&  isNotEmpty($_POST["isbn"])) {
                $isbn = $_POST["isbn"];
            }

            if (isset($_POST["pdate"]) &&  isNotEmpty($_POST["pdate"])) {
                $pdate = $_POST["pdate"];
                $pdate_converted = DateTimeImmutable::createFromFormat("Y-m-d", $pdate);
                if ($pdate_converted !== false) {
                    $pdate = $pdate_converted-> format("Y-m-d");
                }
            }

            if (isset($_POST["publisher"]) &&  isNotEmpty($_POST["publisher"])) {
                $pub_Id = $_POST["publisher"];
            }
            if (isset($_POST["author_ids"])) {
                $book_author_ids = (count($_POST["author_ids"]) == 1 && $_POST["author_ids"][0] == "") ? null : $_POST["author_ids"];
            }
            $data = [
                "title" => $title,
                "isbn" => $isbn,
                "pdate" => $pdate,
                "publisher" => $pub_Id,
                "authors" => $book_author_ids
            ];
            $exito = insertBook($data);
        }
    } catch (Exception $ex) {
        $exito = false;
        echo "<div class=\"alert alert-danger\" role=\"alert\">
   Ha ocurrido una excepción: " . $ex->getMessage() . "</div>";
    }

    ?>
    <div class="container-fluid">
        <header class="mb-5">
            <div class="p-5 text-center " style="margin-top: 58px;">
                <h1 class="mb-3"> Crear libro </h1>

            </div>
        </header>
        <form class='form-control ' method="post">
            <div>
                <label for="title" class="form-label col-3">Título</label>
                <input name="title" type="text" class="form-control col-9" id="title" pattern="^(?!\s*$).+" required />
            </div>
            <div>
                <label for="isbn" class="form-label col-3">ISBN</label>
                <input name="isbn" type="text" class="form-control col-9" id="isbn" pattern="^(?!\s*$).+" />
            </div>

            <div>
                <label for="pdate" class="form-label col-3">Fecha de publicación</label>
                <input name="pdate" type="date" class="form-control col-9" id="pdate" />
            </div>

            <div class='row form-group my-3'>
                <label for="publisher" class="col-form-label col-2">Editorial</label>
                <div class='col-6'>
                    <select name="publisher" id="publisher" class="form-control col-3" required>

                        <option value="" disabled>----</option>
                        <?php
                        if (count($publishers) > 0) :
                            foreach ($publishers as $publisher) :
                        ?>
                                <option value="<?= $publisher["publisher_id"] ?>"><?= $publisher["name"] ?></option>
                        <?php
                            endforeach;
                        endif;
                        ?>


                    </select>
                </div>
            </div>

            <div class="form-group row my-3">
                <label for="authors" class="col-form-label col-2">Autor</label>

                <div class="col-6">
                    <select name="author_ids[]" id="authors" class="form-control" multiple>

                        <option value="">----</option>
                        <?php
                        if (count($authors) > 0) :
                            foreach ($authors as $auth) :
                        ?>
                                <option value="<?= $auth["author_id"] ?>"><?= $auth["completeName"] ?></option>
                        <?php
                            endforeach;
                        endif;
                        ?>



                    </select>
                </div>


            </div>
            <div class="row d-flex justify-content-center">
                <button type="submit" class="btn btn-primary my-3 col-3">Crear libro</button>
            </div>

        </form>
        <a href="listado.php" class="btn btn-link mt-2">Volver</a>

        <?php if (($exito) && isset($_POST["title"])) : ?>
            <div class="alert alert-success" role="alert">
                El libro se ha creado correctamente
            </div>

        <?php endif;


        /**
         * findAllPublishers
         * Crea una consulta con mysqli y obtiene todos los datos de la tabla publishers
         * @return array Array con todas las tuplas de la tabla publishers como array asociativo
         */
        function findAllPublishers(): array
        {
            $conProyecto = getConnection();

            $stmt = $conProyecto->prepare("SELECT * FROM publishers ORDER BY name");

            $stmt->execute();
            $resultado = $stmt->get_result();
            $array = $resultado->fetch_all(MYSQLI_ASSOC);

            return $array;
        }
        /**
         * findAllAuthors
         * Crea una consulta con mysqli y obtiene todos los datos de la tabla authors con su nombre completo (concatenación de first, last y middle name)
         * @return array Array con todas las tuplas de la tabla authors como array asociativo
         */
        function findAllAuthors(): array
        {    //consulta no preparada con mysquli que obtenga un unico array asociativo
            $autores_array = [];
            $resultado = null;
            try {
                $conProyecto = getConnection();
                $consulta = "SELECT author_id,concat(coalesce(last_name, ''), coalesce(first_name, ''), coalesce(middle_name, ' '))AS completeName FROM authors ORDER BY last_name ";
                $resultado = $conProyecto->query($consulta);
                while ($row = $resultado->fetch_assoc()) {
                    array_push($autores_array, $row);
                    //otra opcion en lugar de registro a registro
                    //$autores_array= $resultado->fetch_all(MYSQLI_ASSOC);
                }
            } catch (Exception $e) {
                echo "Ha ocurrido una excepción: " . $e->getMessage();
            } finally {
                if ($resultado != null) {
                    $resultado->close();
                }

                return $autores_array;
            }
        }

        function insertBook(array $data): bool
        {
            $exito = true;
            try {
                $conProyecto = getConnection();
                $conProyecto->begin_transaction();
                $stmt = $conProyecto->prepare("INSERT INTO books (title, isbn, published_date, publisher_id)
                    VALUES (?, ?, ?, ?)");
                $stmt->bind_param("sssi", $data["title"], $data["isbn"], $data["pdate"], $data["publisher"]);
                if (!$stmt->execute()) {
                    throw new Exception("Error al insertar el libro en la tabla.");
                }
                //$book_id = $conProyecto->insert_id;
                if ($data["authors"] != null) {
                    $stmt_b_authors = $conProyecto->prepare("INSERT INTO book_authors (book_id, author_id) VALUES (?, ?)");
                    $book_id = $conProyecto->insert_id;
                    $author_id = $conProyecto->insert_id;
                    $stmt_b_authors->bind_param("ii", $book_id, $author_id);
                    foreach ($data["authors"] as $author_id) {
                        if (!$stmt_b_authors->execute()) throw new Exception("Error al insertar el libro");
                    }
                }
                $conProyecto->commit();
            } catch (Throwable $e) {
                $conProyecto->rollBack();
                $exito = false;
                echo "Error al crear el libro, mensaje: " . $e->getMessage();
            } finally {
                $conProyecto->close();
            }

            return $exito;
        }







        ?>




    </div>
</body>

</html>