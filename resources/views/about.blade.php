<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Slayer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;500;700;900&display=swap"
      rel="stylesheet"
    />

<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link rel="stylesheet" href="{{ asset('css/reset.css') }}">

  </head>
  <body>
    <header>
      <nav>
        <a href="#">Home</a>
        <a href="#">About</a>
        <a href="#">Contact</a>
      </nav>
    </header>

    <div class="slider next">
      <div class="list">
        <div class="item">
          <img src="img/1.jpg" alt="image1" />
          <div class="detail">
            <div class="title">Mobile Legend</div>
            <div class="name">Phoveus Crotus</div>
            <figure>
              <img src="img/avatar/1 - Copy.png" alt="avatar1" />
              <figcaption>
                Lorem / ipsum dolor / sit amet / consectetur/ adipisicing
              </figcaption>
            </figure>
            <div class="desc">
              Lorem ipsum dolor sit amet consectetur adipisicing elit. Ex totam
              quos nesciunt incidunt dolorem. Deleniti nulla odit placeat
              doloremque voluptate iste dicta dolores? Velit officiis tempora
              minima atque architecto est.
            </div>
            <a href="#" class="more">More Details &raquo;</a>
          </div>
        </div>
        <div class="item">
          <img src="img/2.jpg" alt="image2" />
          <div class="detail">
            <div class="title">Mobile Legend</div>
            <div class="nameA">Mamang Alpha</div>
            <figure>
              <img src="img/avatar/1 - Copy.png" alt="avatar2" />
              <figcaption>
                Lorem / ipsum dolor / sit amet / consectetur/ adipisicing
              </figcaption>
            </figure>
            <div class="desc">
              Lorem ipsum dolor sit amet consectetur adipisicing elit. Ex totam
              quos nesciunt incidunt dolorem. Deleniti nulla odit placeat
              doloremque voluptate iste dicta dolores? Velit officiis tempora
              minima atque architecto est.
            </div>
            <a href="#" class="more">More Details &raquo;</a>
          </div>
        </div>

        <!-- nambah -->
          <div class="item">
          <img src="img/3.jpg" alt="image3" />
          <div class="detail">
            <div class="title">Mobile Legend</div>
            <div class="name">Neng Karina</div>
            <figure>
              <img src="img/avatar/1 - Copy.png" alt="avatar3" />
              <figcaption>
                Lorem / ipsum dolor / sit amet / consectetur/ adipisicing
              </figcaption>
            </figure>
            <div class="desc">
              Lorem ipsum dolor sit amet consectetur adipisicing elit. Ex totam
              quos nesciunt incidunt dolorem. Deleniti nulla odit placeat
              doloremque voluptate iste dicta dolores? Velit officiis tempora
              minima atque architecto est.
            </div>
            <a href="#" class="more">More Details &raquo;</a>
          </div>
        </div>
          <div class="item">
          <img src="img/4.jpg" alt="image4" />
          <div class="detail">
            <div class="title">Mobile Legend</div>
            <div class="name">Neng Ixia</div>
            <figure>
              <img src="img/avatar/1 - Copy.png" alt="avatar4" />
              <figcaption>
                Lorem / ipsum dolor / sit amet / consectetur/ adipisicing
              </figcaption>
            </figure>
            <div class="desc">
              Lorem ipsum dolor sit amet consectetur adipisicing elit. Ex totam
              quos nesciunt incidunt dolorem. Deleniti nulla odit placeat
              doloremque voluptate iste dicta dolores? Velit officiis tempora
              minima atque architecto est.
            </div>
            <a href="#" class="more">More Details &raquo;</a>
          </div>
        </div>
      </div>
      <!-- thumnail -->
      <div class="thumbnail">
        <div class="item">
          <img src="img/1.jpg" alt="thumbnail 1" />
          <div class="detail">
            <div class="name1">Phoveus Crotus</div>
            <blockquote>
              "Lorem, ipsum dolor sit amet consectetur adipisicing."
            </blockquote>
          </div>
        </div>
        <div class="item">
          <img src="img/2.jpg" alt="thumbnail 2" />
          <div class="detail">
            <div class="name2">Mamang alpha</div>
            <blockquote>
              "Lorem, ipsum dolor sit amet consectetur adipisicing."
            </blockquote>
          </div>
        </div>
        <!-- nambah -->
        <div class="item">
          <img src="img/3.jpg" alt="thumbnail 3" />
          <div class="detail">
            <div class="name2">Mamang alpha</div>
            <blockquote>
              "Lorem, ipsum dolor sit amet consectetur adipisicing."
            </blockquote>
          </div>
        </div>
        <div class="item">
          <img src="img/4.jpg" alt="thumbnail 4" />
          <div class="detail">
            <div class="name2">Mamang alpha</div>
            <blockquote>
              "Lorem, ipsum dolor sit amet consectetur adipisicing."
            </blockquote>
          </div>
        </div>
      </div>
<!-- arrows -->
      <div class="arrows">
        <button id="prev">&lt;</button>
        <button id="next">&gt;</button>
      </div>

<!-- loadir -->
 <div class="loading-bar"></div>
    </div>
<script src="{{ asset('js/script.js') }}"></script>
  </body>
</html>
