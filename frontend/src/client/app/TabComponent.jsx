import React from "react";
import Globals from "./globals.jsx";

var classnames = require('classnames');
var Iframe = require("react-iframe");

const tabStyle = {
    overflow: "hidden",
    border: '1px solid #ccc',
    backgroundColor: '#f1f1f1',
};
const tabStyleHidden = {
    display: 'none',
    overflow: "hidden",
    border: '1px solid #ccc',
    backgroundColor: '#f1f1f1',
};

const tabTextStyle = {
    float: 'left',
    display: 'block',
    color: 'black',
    textAlign: 'center',
    padding: '14px 16px',
    textDecoration: 'none',
    transition: '0.3s',
    fontSize: '17px',
};

const tabTextActiveStyle = {
    float: 'left',
    display: 'block',
    color: 'black',
    textAlign: 'center',
    padding: '14px 16px',
    textDecoration: 'none',
    transition: '0.3s',
    fontSize: '17px',
    backgroundColor: '#ddd',
};

class TabComponent extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            urls: [],
            activeMenuItemUid: 0,
            secondsElapsed: 0,
            loopTime: 50,
            apiSettings: {
                rotate_speed: 60,
                show_tabs: true
            }
        };
        this.setActiveMenuItem = this.setActiveMenuItem.bind(this);
        this.tick = this.tick.bind(this);
    }

    tick() {
        this.setState({secondsElapsed: this.state.secondsElapsed + 1});
        if (this.state.secondsElapsed % this.state.apiSettings.rotate_speed == 0) {
            if (this.state.activeMenuItemUid < this.state.urls.length - 1) {
                this.setState({activeMenuItemUid: this.state.activeMenuItemUid + 1});
            } else {
                this.setState({activeMenuItemUid: 0});
            }
        }
    }

    componentDidMount() {
        this.getUrlsFromApiAsync();
        this.getSettingsFromApiAsync();
        this.interval = setInterval(this.tick, 1000);
    }

    componentWillUnmount() {
        clearInterval(this.interval);
    }

    getUrlsFromApiAsync() {
        return fetch(Globals.api_endpoint + '/api/urls')
            .then((response) => response.json())
            .then((responseJson) => {
                this.setState({urls: responseJson.urls});
            })
            .catch((error) => {
                console.error(error);
            });
    }

    getSettingsFromApiAsync() {
        return fetch(Globals.api_endpoint + '/api/settings')
            .then((response) => response.json())
            .then((responseJson) => {
                console.log("RESPONSE");
                this.setState({apiSettings: responseJson.settings});
                console.log(this.state.apiSettings);
            })
            .catch((error) => {
                console.error(error);
            });
    }

    setActiveMenuItem(uid) {
        this.setState({activeMenuItemUid: uid});
    }

    render() {
        if (typeof this.state.urls[0] == 'undefined') {
            return <div>Loading...</div>;
        }

        //Build tabs
        var tabs = [];
        this.state.urls.forEach(function (url, key) {
            tabs.push(<MenuItem active={(this.state.activeMenuItemUid == key)} key={key}
                                onSelect={this.setActiveMenuItem} uid={url.title} selectKey={key}/>);
        }, this);

        //Get active url
        let activeUrl = this.state.urls[this.state.activeMenuItemUid].url;
        let tabStyling = this.state.apiSettings.show_tabs ? tabStyle : tabStyleHidden;
        return (
            <div>
                <div className="tab-container" style={tabStyling}>{tabs}</div>
                <Iframe url={activeUrl}/>
            </div>
        );
    }
}

class MenuItem extends React.Component {

    constructor(props) {
        super(props);
        // This binding is necessary to make `this` work in the callback
        this.handleClick = this.handleClick.bind(this);
    }

    handleClick(event) {
        event.preventDefault();
        this.props.onSelect(this.props.selectKey);
    }

    render() {
        let className = this.props.active ? 'active' : null;
        let styling = this.props.active ? tabTextActiveStyle : tabTextStyle;
        let href = "#" + this.props.uid;
        return (
            <a href={href} style={styling} className={className} onClick={this.handleClick}>{this.props.uid}</a>
        );
    }
}

export default TabComponent;