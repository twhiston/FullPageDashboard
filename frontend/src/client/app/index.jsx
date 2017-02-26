import React from 'react';
import {render} from 'react-dom';
import TabComponent from './TabComponent.jsx';

class App extends React.Component {
    render () {
        return (
            <div>
                <TabComponent />
            </div>
        );
    }
}

render(<App/>, document.getElementById('app'));
