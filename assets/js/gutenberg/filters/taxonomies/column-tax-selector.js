/**
 * WARNING: Removed from import, this file is not in use any more, but kept for future reference when similar functions are needed.
 */

import { Component } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { SelectControl } from '@wordpress/components';

const DEFAULT_QUERY = {
  orderby: 'name',
  order: 'asc',
  _fields: 'id,name',
};
class ColumnTaxonomyMetabox extends Component {
  constructor(props) {
    super(props);

    this.state = {
      availableTerms: []
    }
    // Bindings
    this.onChangeTerm = this.onChangeTerm.bind(this);
  }

  componentDidMount() {
    this.fetchTerms();
  }

  fetchTerms() {
    const { taxonomy } = this.props;
    if (!taxonomy) {
      return;
    }

    apiFetch({
      path: addQueryArgs(`/wp-json/wp/v2/${taxonomy.rest_base}`, DEFAULT_QUERY),
    })
    .then(
      terms => { // resolve
        // console.log('terms', terms);
        this.setState({availableTerms: terms});
      },
      xhr => { // reject
        if (xhr.statusText === 'abort') {
          return;
        }
      }
    );
  }

  onChangeTerm( col ) {
    const { onUpdateTerms, taxonomy } = this.props;
    // this.props.onUpdateTerms(newTerms, taxonomy.rest_base)
    // taxonomy: caps, description, labels, name, rest_base, slug, types

    if (col === -1) {
      onUpdateTerms([], taxonomy.rest_base);
    } else {
      onUpdateTerms([col], taxonomy.rest_base);
    }
  }

  render(){
    const { terms } = this.props;
    const selectTermId = terms.length == 0 ? -1 : terms[0];
    // console.log('proops', this.props);

    const termOptions = this.state.availableTerms.map(term => ({ value: term.id, label: term.name }));
    return (
      <>
        <SelectControl
          label="如果是专栏文章，请选择一个专栏分类"
          value={selectTermId}
          options={[
            { value: -1, label: '非专栏文章' },
            ...termOptions
          ]}
          onChange={this.onChangeTerm}
          help={<p>专栏包括<b>东篱说</b>, <b>狗粮</b>, <b>摄影</b></p>}
        />

      </>
    );
  }
}


export default OriginalComponent => props => (props.slug === 'column') ? <ColumnTaxonomyMetabox {...props} /> : <OriginalComponent {...props} />;