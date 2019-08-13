import { PluginPostStatusInfo } from '@wordpress/edit-post';
import { withSelect, withDispatch } from '@wordpress/data';
import { compose } from '@wordpress/compose';
import { TextControl } from '@wordpress/components';

const PageIconExtension = (props) => {
  const helpMessage = <>
    <p style={{marginTop: '1rem'}}>为本页设置一个图标，此图标应用于手机页面的顶部导航栏(如果此页面添加到导航栏中的话)</p>
    <p>打开<a href="https://fonts.nucssa.org/nucssa-icon-font/" target="_blank" style={{color: 'darkblue', fontWeight: 'bold'}}>此链接</a>查看可用的图标，填入其名字。</p>
    <p>如果上面网页中没有你想用的图标，请联系IT添加想用的图标。</p>
  </>;
  const style = {
    icon: {
      alignSelf: 'start',
      marginTop: '1.2rem',
      marginLeft: '1rem',
      fontSize: '2rem',
      width: '70px',
    }
  }
  return (
    <PluginPostStatusInfo className="nucssa-theme-page-icon">
      <TextControl
        type="text"
        value={props.pageIcon}
        label="Page Icon"
        help={helpMessage}
        onChange={props.changePageIcon}
      />
      <i className={`icon ${props.pageIcon}`} style={style.icon}></i>
    </PluginPostStatusInfo>
  );
}

const store = 'core/editor';
const metaKey = '_page_icon';
const mapStateToProps = withSelect( select => {
  return {
    pageIcon: select(store).getEditedPostAttribute('meta')[metaKey]
  };
});
const mapDispatchToProps = withDispatch( dispatch => {
  const currentMetas = wp.data.select(store).getEditedPostAttribute('meta');
  return {
    changePageIcon: icon => dispatch(store).editPost({
      meta: {
        ...currentMetas,
        [metaKey]: icon
      }
    }),
  };
});

export default compose(
  mapStateToProps,
  mapDispatchToProps
)(PageIconExtension);