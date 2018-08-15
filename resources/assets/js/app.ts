import Router from "./modules/router";

Router.on("RootController@index", "language/index");
Router.on("LanguagePageController@index", "language/index");
Router.run();