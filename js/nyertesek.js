const menuItems = [
    { id: "zsuri", name: "Zsűri értékelése", videa: "" },
    { id: "mandula", name: "Mandula Máté", videa: "o2XrJkU048LEs5h1" },
    { id: "toth", name: "Tóth Janka", videa: "A35JPXGueHpUhWyR"},
    { id: "szelig", name: "Szélig Balázs", videa: "i4ZT9Nl0tqya8owp" },
    { id: "horvath", name: "Horváth Dóra", videa: "ButTe6ZsstlHUJtY" },
    { id: "nemeth_csapo", name: "Németh Vince & Csapó Tibor", videa: "Dr1VROUXxI1PZ0qT" },
    { id: "kelemen", name: "Kelemen Boldizsár", videa: "nv1G6vdYB2RFoDa8" },
    { id: "vig", name: "Víg Örs", videa: "NwAUCpVwo1iQJuJL" },
    { id: "ihrazi", name: "Ihrázi Dóra Johanna", videa: "DJXMCoLAUCajWkch" },
    { id: "hadnagyok", name: "Hadnagy Mózes & Hadnagy Iringó", videa: "KgdhAODsNqAOnzmT" },
    { id: "krolikowski", name: "Krolikowski Ambrus Ede", videa: "k5QtYTOAlSIjufIC" },
];

let currentMenuItem = 0;


// Select the <ul> element inside the main menu
const menuList = document.querySelector("#main-menu ul");

// Dynamically create <li> elements for each menu item
menuItems.forEach(item => {
    // Create <li> element
    const listItem = document.createElement("li");

    // Create <a> element
    const link = document.createElement("a");
    link.href = `#${item.id}`; // Set the href attribute
    link.textContent = item.name; // Set the text content

    // Append <a> to <li>, and <li> to <ul>
    listItem.appendChild(link);
    menuList.appendChild(listItem);
});


var lan = "hu";

function switchLan(){
  if (lan=="hu"){
    lan="en";
  }
  else{
    lan="hu";
  }
  updateLan();
}

function updateLan(){
    if (lan=="en"){
        document.querySelectorAll('.hungarian').forEach(function(div) {
        div.style.display = 'none';
      });
        document.querySelectorAll('.english').forEach(function(div) {
        div.style.display = '';
      });
    }
      else{
        document.querySelectorAll('.hungarian').forEach(function(div) {
        div.style.display = '';
      });
        document.querySelectorAll('.english').forEach(function(div) {
        div.style.display = 'none';
      });
    }
}


let debug = "NONE";
// MENU OPEN AND CLOSE

$(document).on("click", function (event) {
  $(".menu").each(function () {
    let $menu = $(this);
    let $toggle = $("#" + $menu.attr("id") + "-toggle");

    if (
      $menu.is(":visible") &&
      !$menu.is(event.target) &&
      !$menu.has(event.target).length
    ) {
      $menu.hide();
      $toggle.removeClass("open");
    }
  });
});

$(".menu-toggle").on("click", function (event) {
  let $toggle = $(this);
  let $menu = $("#" + $toggle.attr("id").replace("-toggle", ""));

  if (!$toggle.hasClass("open")) {
    $(document).trigger("click");
    event.stopPropagation();
    $menu.toggle();
    $toggle.toggleClass("open");
  } else {
    $(document).trigger("click");
  }
});

$("#main-menu a").on("click", function (event) {
  $("#main-menu").hide();
  $("#main-menu-toggle").removeClass("open");
});

// switch on mobile between content and music maker

$("#music-maker-opener").on("click", function (event) {
  //new AudioContext().resume();
  event.stopPropagation();
  $("#music-maker").toggle();
  $("#music-maker-opener").hide();
  $("#content-opener").toggle();
  $("#content").hide();
  $("body").css("background-color", "var(--secondcolor)");
});

$("#content-opener").on("click", function (event) {
  event.stopPropagation();
  $("#music-maker").hide();
  $("#music-maker-opener").toggle();
  $("#content-opener").hide();
  $("#content").toggle();
  $("body").css("background-color", "var(--firstcolor)");
});

// LOAD PART OF HTML TO CONTENT
$(window).on("load", function () {
    var fragment = window.location.hash.substring(1);
    switch(fragment){
      case "en":
        lan = "en";
      case "":
        window.location.hash = "#zsuri";
    }
    loadContentFromHash();
});

$(window).on("hashchange", function () {
  loadContentFromHash();
});

  

    // Function to generate a random harmonious color
    function randomHarmoniousColor(baseHue) {
      const harmonizeHue = (Math.random() * 360) % 360; // Any hue
      const saturation = Math.random() * 20 + 70; // 70%-90% saturation for vibrancy
      const luminance = Math.random() * 20 + 30; // 30%-50% luminance for darker shades

      // Convert HSL to HEX
      const hslToHex = (h, s, l) => {
          s /= 100;
          l /= 100;
          const c = (1 - Math.abs(2 * l - 1)) * s;
          const x = c * (1 - Math.abs((h / 60) % 2 - 1));
          const m = l - c / 2;
          let r = 0, g = 0, b = 0;

          if (h >= 0 && h < 60) { r = c; g = x; b = 0; }
          else if (h >= 60 && h < 120) { r = x; g = c; b = 0; }
          else if (h >= 120 && h < 180) { r = 0; g = c; b = x; }
          else if (h >= 180 && h < 240) { r = 0; g = x; b = c; }
          else if (h >= 240 && h < 300) { r = x; g = 0; b = c; }
          else if (h >= 300 && h < 360) { r = c; g = 0; b = x; }

          r = Math.round((r + m) * 255);
          g = Math.round((g + m) * 255);
          b = Math.round((b + m) * 255);

          return `#${((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1).toUpperCase()}`;
      };

      return hslToHex((baseHue + harmonizeHue) % 360, saturation, luminance);
  }

function loadContentFromHash() {
  // set stuff up animation things
  $("#bg-animation").show();
  window.scrollTo(0, 0);

  var fragment = "zsuri";
  // Get the URL fragment (the part after #)
  if (window.location.hash.substring(1) != undefined) {
    var fragment = window.location.hash.substring(1); // This removes the '#' from the fragment
  } else {
    window.location.hash = "#zsuri";
  }

  if (fragment != "zsuri" && fragment != "intro") {
    const item = menuItems.find(({ id }) => id === fragment);
    $("#music-maker iframe").attr(
      "src",
      "//videa.hu/player?v="+ item.videa
    );
  } 

  
  // Reload the video to apply the new source
  //$("#music-maker iframe")[0].load();
 
  $("#content").load("nyertesek_pages/description/" + fragment + ".html?ver="+version, function () {
    updateLan();
    $("#bg-left").toggleClass("closed");
    $("#loading").hide();

    // attach advertisement
    if (fragment == "zsuri" || fragment == "intro") {
      $.get("assets/advertisement/advertisement.html?ver="+version, function (adContent) {
        if ($("div#advertisement").length == 0) {
          $("#content").append(adContent);
        }
      });
    }

    if (fragment == "zsuri"){
      $('#music-maker span').remove();
      $("#music-maker img").css("display","none");
      $("#music-maker-video").css("display","none");
      $("#music-maker").css("padding", 0)
      $("#music-maker-opener").css("display","none");
        menuItems.forEach(item => {
          const color = randomHarmoniousColor(208);
          $('#music-maker').append(
              `<span style="background-color: ${color}" class="nyertesek_items"><a href="#${item.id}">${item.name}</a></span> `
          );
      });
    } else if (fragment == "intro"){
      $("#music-maker img").css("display","");
      $("#music-maker-video").css("display","none");
      $('#music-maker span').remove();
    } else {
      $("#music-maker img").css("display","none");
      $("#music-maker-video").css("display","");
      $("#music-maker").css("padding", "");
      $("#music-maker-opener").css("display","");
      $('#music-maker span').remove();
    }
    setTimeout(function () {
      $("#bg-right").toggleClass("closed");
    }, 400);

    setTimeout(function () {
      // set up stuff for next round
      $("#bg-animation").hide();
      $("#bg-right").toggleClass("closed");
      $("#bg-left").toggleClass("closed");

      // set colors to old one
      var firstcolor = getComputedStyle(document.documentElement)
        .getPropertyValue("--firstcolor")
        .trim();
      document.documentElement.style.setProperty(
        "--firstcolor_prev",
        firstcolor
      );
      var secondcolor = getComputedStyle(document.documentElement)
        .getPropertyValue("--secondcolor")
        .trim();
      document.documentElement.style.setProperty(
        "--secondcolor_prev",
        secondcolor
      );
    }, 2000);
  });
}
