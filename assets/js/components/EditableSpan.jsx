import { Component, createRef } from '@wordpress/element';
/**
 * Props:
 *  - type: (optional) 'number'|'text'|'email', Default 'text'. input content type, used for validation.
 *  - placeholder: (Required) placeholder text when there is no value
 *  - value: (Required)
 *  - onChange: (Required) handler for value change
 */
export default class EditableSpan extends Component {
  constructor(props) {
    super(props);
    this.state = { isEditing: false };
    this.enterEditingMode = this.enterEditingMode.bind(this);
    this.refInput = createRef();
  }

  componentDidUpdate() {
    if (this.state.isEditing) {
      this.refInput.current.focus();
    }
  }

  enterEditingMode() {
    this.setState({ isEditing: true });

  }

  render() {
    if (this.state.isEditing) {
      const width = (this.props.value.length || this.props.placeholder.length) * 2 + 1 + 'ex';
      return <input ref={this.refInput} type={this.props.type} style={{ width }} placeholder={this.props.placeholder} value={this.props.value} onChange={e => this.props.onChange(e.currentTarget.value)} onBlur={() => this.setState({ isEditing: false })} />;
    } else {
      return <span onClick={this.enterEditingMode} tabIndex="0" onFocus={this.enterEditingMode}>{this.props.value || this.props.placeholder}</span>;
    }
  }
}