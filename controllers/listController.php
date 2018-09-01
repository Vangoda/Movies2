<?php
class ListController{
    public static function drawMovieTable(){
        foreach(Movie::fillMovieTable() as $movie):?>
              <tr>
                <div class='container'><td class="list-image"><img class='img-responsive crop' src="../image/<?= $movie['imgPath'] ?>" alt=""></td></div>
                <td><?= $movie['title'] ?></td>
                <td><?= $movie['year'] ?></td>
                <td><?= $movie['runtime'] ?></td>
                <td><a href="../controllers/inputController.php?deleteID=<?= $movie['id'] ?>">Obriši</a></td>
              </tr>
        <?php
        endforeach;
    }
}