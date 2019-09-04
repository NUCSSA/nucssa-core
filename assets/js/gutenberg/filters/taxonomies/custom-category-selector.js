import { Component } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { SelectControl } from '@wordpress/components';

const DEFAULT_QUERY = {
  per_page: -1,
  orderby: 'name',
  order: 'asc',
  _fields: 'id,name,parent',
};
class CategorySelectorMetabox extends Component {
  constructor(props) {
    super(props);

    this.state = {
      availableTerms: [],
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
      path: addQueryArgs(`/wp/v2/${taxonomy.rest_base}`, DEFAULT_QUERY),
    })
    .then(
      terms => { // resolve
        const rootTerm = {id: 0};
        const nestedTerms = this.buildNestedTerms(terms, rootTerm);
        this.setState({availableTerms: nestedTerms.children});
      },
      xhr => { // reject
        if (xhr.statusText === 'abort') {
          return;
        }
      }
    );
  }

  buildNestedTerms(terms, parent) {
    let childTerms = terms.filter(term => term.parent === parent.id);
    childTerms.forEach( term => {
      if (parent.id === 0){
        term.parent = null;
      } else {
        term.parent = parent;
      }
      return this.buildNestedTerms(terms, term);
    } );
    parent.children = childTerms;

    return parent;
  }

  onChangeTerm( termId ) {
    const { onUpdateTerms, taxonomy } = this.props;
    // this.props.onUpdateTerms(newTerms, taxonomy.rest_base)
    // taxonomy: caps, description, labels, name, rest_base, slug, types

    if (termId === -1) {
      onUpdateTerms([], taxonomy.rest_base);
    } else {

      // find all parent terms
      console.log('all terms', this.state.availableTerms);
      console.log('raw terms', this.state.rawTerms);

      // get all leaf nodes
      if (term.children.length === 0)
        result.concat(term);
      else {
        // repeat on term.children
      }


      onUpdateTerms([termId], taxonomy.rest_base);
    }
  }

  render(){
    // console.log('proops', this.props);
    const { terms } = this.props;
    const selectTermId = terms.length == 0 ? -1 : terms[0];

    const buildOptions = (term, depth = 0) => {

      let options = [{ value: term.id, label: '  '.repeat(depth) + '--'.repeat(depth) + term.name, disabled: term.children.length > 0 }];
      if (term.children.length > 0) {
        const optionsFromChildren = term.children.map(t => buildOptions(t, depth + 1)).flat();
        options = options.concat(optionsFromChildren);
      }

      return options;
    };
    const termOptions = this.state.availableTerms.map(term => buildOptions(term)).flat();
    console.log('options', termOptions);
    return (
      <>
        <SelectControl
          value={selectTermId}
          options={termOptions}
          onChange={this.onChangeTerm}
        />

      </>
    );
  }
}


export default OriginalComponent => props => (props.slug === 'category') ? <CategorySelectorMetabox {...props} /> : <OriginalComponent {...props} />;