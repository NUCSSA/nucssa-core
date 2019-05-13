import React, {Component} from 'react';
import PropTypes from 'prop-types';
import './SearchDropdown.scss';

class SearchDropdown extends Component {
  constructor(props) {
    super(props);
    this.state = {
      matches: [],
      selfDismissDropdown: false,
      value: '',
    };

    this.search = this.search.bind(this);
    this.onKeyDown = this.onKeyDown.bind(this);
    this.onBlur = this.onBlur.bind(this);
    this.onSelection = this.onSelection.bind(this);

    this.ulNodeRef = React.createRef();
    this.inputNodeRef = React.createRef();
  }

  async search(e) {
    const keyword = e.currentTarget.value;
    const matches = await this.props.search(keyword.trim());
    this.setState({ matches, value: keyword });
  }

  onKeyDown(e) {
    // console.log("key event", e);
    // debugger;
    this.setState({selfDismissDropdown: false});
    if (this.props.shouldDropdownShown && !this.state.selfDismissDropdown && this.state.matches.length > 0) {


      // get current active node
      let currentActiveLiNode = this.ulNodeRef.current.querySelector('li.active');
      let nextActiveLiNode = null;
      // debugger;
      switch (e.key) {
        case 'ArrowDown':
          // find the current active node, move to its next sibling
          // if no node is active, make the first one active
          if (currentActiveLiNode) {
            nextActiveLiNode = currentActiveLiNode.nextSibling || this.ulNodeRef.current.firstChild;
          } else {
            nextActiveLiNode = this.ulNodeRef.current.firstChild;
          }
          e.preventDefault();
          break;

        case 'ArrowUp':
          if (currentActiveLiNode) {
            nextActiveLiNode = currentActiveLiNode.previousSibling || this.ulNodeRef.current.lastChild;
          } else {
            nextActiveLiNode = this.ulNodeRef.current.lastChild;
          }
          e.preventDefault();
          break;

        case 'Escape':
          this.setState({selfDismissDropdown: true});
          return;

        case 'Enter':
          if (currentActiveLiNode){
            // find the match related to this node and call `onSelect` on it.
            // debugger;
            const i = Array.prototype.findIndex.call(this.ulNodeRef.current.children, (node) => node === currentActiveLiNode);
            this.onSelection(this.state.matches[i]);
          }
          return;

        default:
          return;
      }

      if (currentActiveLiNode !== nextActiveLiNode) {
        currentActiveLiNode && currentActiveLiNode.classList.remove('active');
        nextActiveLiNode.classList.add('active');
      }
    }
  }

  onSelection(match){
    const value = this.props.searchFieldValueOnSelection(match);
    this.setState({selfDismissDropdown: true, value});
    this.inputNodeRef.current.blur();
  }

  onBlur(e) {
    this.setState({selfDismissDropdown: true});
  }

  makeCurrentElementActive(e) {
    e.currentTarget.classList.add('active');
  }

  makeCurrentElementInactive(e){
    e.currentTarget.classList.remove('active');
  }

  controlElementHTML() {
    return (
      <label>
        <i className="dashicons dashicons-search" />
        <input type="text" value={this.state.value} onChange={this.search} onKeyDown={this.onKeyDown} ref={this.inputNodeRef} />
      </label>
    );
  }

  dropDownHTML() {
    return (
      <ul className="dropdown-items" ref={this.ulNodeRef}>
        {this.state.matches.length === 0 ? (
          <li>{this.props.noMatchMessage}</li>
        ) : (
          this.state.matches.map(match => (
            <li
              key={match.key}
              onMouseDown={() => {console.log('>>> called'); this.onSelection(match);}}
              onMouseOver={this.makeCurrentElementActive}
              onMouseOut={this.makeCurrentElementInactive}
              tabIndex="0"
            >
              {this.props.renderMatchItem(match)}
            </li>
          ))
        )}
      </ul>
    );
  }

  render() {
    return (
      <div className="search-dropdown" onBlur={this.onBlur} tabIndex="0">
        {this.controlElementHTML()}
        {(this.props.shouldDropdownShown && !this.state.selfDismissDropdown) && this.dropDownHTML()}
      </div>
    );
  }
}

SearchDropdown.propTypes = {
  search: PropTypes.func.isRequired,
  renderMatchItem: PropTypes.func.isRequired, // match item must have a key prop
  shouldDropdownShown: PropTypes.bool.isRequired,
  searchFieldValueOnSelection: PropTypes.func.isRequired,
  noMatchMessage: PropTypes.element,
};

export default SearchDropdown;