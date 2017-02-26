/**
 * Created by twhiston on 25.02.17.
 */
var rotateTime = 30; //Should be the var speed for rotation
var counter = rotateTime;
var timer = null;
var urls = null;


function httpGetAsync(theUrl, callback)
{
    var xmlHttp = new XMLHttpRequest();
    xmlHttp.onreadystatechange = function() {
        if (xmlHttp.readyState == 4 && xmlHttp.status == 200)
            callback(xmlHttp.responseText);
    }
    xmlHttp.open("GET", theUrl, true); // true for asynchronous
    xmlHttp.send(null);
}


function resize() {
    $('.tab-content').css('height', $(window).height() - $('.nav-tabs').height() - 5);
}

function nextTab() {
    var next = $('.nav-tabs li.active').next('li');
    if (next.text() == '+') {
        $($('.nav-tabs li')[2]).find('a').click();
    } else {
        $('.nav-tabs li.active').next('li').find('a').click();
    }
    document.title = 'Full Page Dashboard - ' + $('.nav-tabs li.active a').text();
}

function play() {
    timer = setInterval(function() { tick(); }, 1000);
    $('#pause-btn').removeClass('hidden');
    $('#play-btn').addClass('hidden');
}

function stop() {
    clearInterval(timer);
    $('#pause-btn').addClass('hidden');
    $('#play-btn').removeClass('hidden');
}

function initKnob(maxValue) {
    $("#counter").knob({
        width: 20,
        height: 20,
        displayInput: false,
        min: 0,
        max: maxValue,
        readOnly: true
    });
}

$(document).ready(function() {
    // <?php if (count($urls) > 1) : ?>
    initKnob(rotateTime);
    play();
    httpGetAsync('http://fpd.dev/api/urls',setUrls);
    document.title = 'Full Page Dashboard - ' + $('.nav-tabs li.active a').text();
    // <?php endif; ?>
    resize();
});

function setUrls(text){
    urls = JSON.parse(text)
    console.log(urls);
}

function tick() {
    $('#counter').val(counter).trigger('change');
    counter--;
    if (counter < 0) {
        counter = rotateTime;
        nextTab();
    }
}

$(window).resize(function() {
    resize();
});

function deleteUrl() {
    var title = $('.nav-tabs li.active a').text();
    var id = $('.nav-tabs li.active a').attr('href').replace('#url', '');

    var r = confirm('Are you sure you would to delete "' + title + '"');
    if (r == true) {
        $.ajax({
            type: "POST",
            url: 'api.php',
            data: {'action': 'delete', 'title': title, 'id': id},
            success: function(data, textStatus, jqXHR) {
                window.location.reload()
            }
        });
    }
}

function isNormalInteger(str) {
    var n = ~~Number(str);
    return String(n) === str && n >= 0;
}

function changeCounter() {
    var newCounter = prompt("Select rotate time", rotateTime);
    if (newCounter != null && isNormalInteger(newCounter)) {
        newCounter = parseInt(newCounter);
        $.ajax({
            type: "POST",
            url: 'api.php',
            data: {'action': 'speed', 'value': newCounter},
            success: function(data, textStatus, jqXHR) {
                rotateTime = newCounter;
                $('#counter').trigger('configure', {
                    "max": rotateTime,
                });
                counter = 0;
            }
        });
    }
}

function addUrl() {
    var title = $('#add-title').val();
    var url = $('#add-url').val();

    if (title == '' || url == '') {
        alert('Title and URL cannot be empty');
    }

    $.ajax({
        type: "POST",
        url: 'api.php',
        data: {'action': 'add', 'title': title, 'url': url},
        success: function(data, textStatus, jqXHR) {
            window.location.reload()
        }
    });
}