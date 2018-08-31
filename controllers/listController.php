<?php
class ListController{
    public static function drawMovieTable(){
        foreach(Movie::fillMovieTable() as $movie):?>
              <tr>
                <td class="list-image"><img src="../image/<?= $movie['imgPath'] ?>"" alt=""></td>
                <td>{{$movie->title}}</td>
                <td>{{$movie->year}}</td>
                <td>{{$movie->runtime}}</td>
                <td><a href="deleteSelected/{{$movie->id}}">Obriši</a></td>
              </tr>
        <?php
        endforeach;
    }
}