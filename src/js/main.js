var pagesAll = 0;
var page = 1;
var currentPage = 'page'+page;
var currentMon = -1;
var paginationElement = $('.pagination')[0];
var prevElement = $(paginationElement).firstChild;
var preloader = $('.preloader-container');

$( document ).ready(function() {
	getAllNews();
});

function getAllNews() {
    if (currentMon != 0) {
        currentMon = 0;
        togglePreload();
        $('.newsContainer').html("");
        $.getJSON('/getNews.php', function(data){
            //console.log(data);
            pages = data.news[0];
            pagesAll = data.news[1];
            pagesAll = pagesAll.allPage;
            setPagination();
            $.each(pages[currentPage], function(){
                if (this.status !== undefined) {
                    return this.status;
                }
                //console.log(this);
                $('.newsContainer').append('<div class="newsView"><div class="newsDescription text-justify">'+this.description+'</div><a class="newsLink" data-id="'+this.id+'">Подробнее</a></div>');
            });
            setActionLink();
            togglePreload();
        });
    }
}

function setActionPagination() {
    $('li.page-item a').on('click', function() {
        if (this.dataset.page === undefined) {
            if (this.dataset.direction == 'prev' && !($(this.parentElement).hasClass('disabled'))) {
                page -= 1;
                toggleClass($('li.page-item a')[page]);
                getPageNews(page);
            } else if (this.dataset.direction == 'next' && !($(this.parentElement).hasClass('disabled'))) {
                page += 1;
                toggleClass($('li.page-item a')[page]);
                getPageNews(page);
            }
        } else {
            if (this.dataset.page != page.toString()) {
                page = Number(this.dataset.page);
                toggleClass(this);
                getPageNews(page);
            }
        }
    });
}

function setActionLink() {
    $('a.newsLink').on('click', function() {
        getFullNews(this);
    });
}

function toggleClass(elem) {
    if (elem.dataset.page == pagesAll) {
        $('li.page-item').removeClass('active').removeClass('disabled');
        $(elem.parentElement.nextElementSibling).addClass('disabled');
        $(elem.parentElement).addClass('active');
        console.log('Последняя');
    } else if (elem.dataset.page == '1') {
        $('li.page-item').removeClass('active').removeClass('disabled');
        $(elem.parentElement.previousElementSibling).addClass('disabled');
        $(elem.parentElement).addClass('active');
        console.log('Первая');
    } else {
        $('li.page-item').removeClass('active').removeClass('disabled');
        $(elem.parentElement).addClass('active');
        console.log(elem.dataset.page);
    }
}

function getFullNews(elem) {
    currentMon = -1;
    togglePreload();
    $('.newsContainer').html("");
    $(paginationElement).html("");
    $.getJSON('/getNews.php?id=' + elem.dataset.id, function(data){
        //console.log(data);
        fullNews = data.news[0];
        //console.log(fullNews);
        fullNews = fullNews.full[0];
        //pagesAll = data.news[1];
        //pagesAll = pagesAll.allPage;
        //setPagination();
        //console.log(fullNews);
        $('.newsContainer').append('<div class="newsView"><div class="fullNews" data-id="'+fullNews.id+'"><h1>'+fullNews.title+'</h1><blockquote  class="text-justify">'+fullNews.text+'<footer>@Author: <cite>'+fullNews.author+'</cite><div class="date-news">'+fullNews.date+'</div></footer></blockquote>');
        togglePreload();
    });
}

function getMonNews(elem) {
    if (currentMon != elem.dataset.number) {
        togglePreload();
        $('.newsContainer').html("");
    	$.getJSON('/getNews.php?mon=' + elem.dataset.number, function(data){
    		currentMon = elem.dataset.number;
            //console.log(data);
            pages = data.news[0];
            pagesAll = data.news[1];
            pagesAll = pagesAll.allPage;
            setPagination();
            $.each(pages[currentPage], function(){
            	if (this.status !== undefined) {
            		return this.status;
            	}
            	//console.log(this);
                $('.newsContainer').append('<div class="newsView"><div class="newsDescription text-justify">'+this.description+'</div><a class="newsLink" data-id="'+this.id+'">Подробнее</a></div>');
            });
            setActionLink();
            togglePreload();
        });
    }
}

function getPageNews(pageCurrent) {
    togglePreload();
    var link = false;
    $('.newsContainer').html("");
    if (location.search.match(/mon/gi) != null) {
        link = '/getNews.php?mon=' + location.search.match(/\d.?/gi)[0] + '&page=' + pageCurrent;
    } else {
        link = '/getNews.php?page=' + pageCurrent;
    }
    $.getJSON(link, function(data){
        //console.log(data);
        pages = data.news[0];
        pagesAll = data.news[1];
        pagesAll = pagesAll.allPage;
        $.each(pages['page'+pageCurrent], function(){
            if (this.status !== undefined) {
                return this.status;
            }
            //console.log(this);
            $('.newsContainer').append('<div class="newsView"><div class="newsDescription text-justify">'+this.description+'</div><a class="newsLink" data-id="'+this.id+'">Подробнее</a></div>');
        });
        setActionLink();
        togglePreload();
    });
}

function setPagination() {
    $(paginationElement).html("");
    $(paginationElement).append('<li class="page-item disabled"><a class="page-link" data-direction="prev">Предыдущая</a></li>');
    //console.log(pagesAll);
    for (page = 1; page <= pagesAll; page+=1) {
        if (page == 1) {
            $(paginationElement).append('<li class="page-item active"><a class="page-link" data-page="1">1</a></li>');
            //console.log(page);
        } else {
            $(paginationElement).append('<li class="page-item"><a class="page-link" data-page="' +  page + '">' + page + '</a></li>');
            //console.log(page);
        }
    }
    $(paginationElement).append('<li class="page-item"><a class="page-link" data-direction="next">Следующая</a></li>');
    page = 1;
    setActionPagination();
}

function togglePreload() {
        window.scrollTo(0,0);
        if (preloader.css('display') == 'none') {
            preloader.animate({opacity: "toggle"}, 0, "linear");
        } else {
            setTimeout(function() {
                preloader.animate({opacity: "toggle"}, 300, "linear");
            }, 500); 
        }   
}