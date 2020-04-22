/**
 * Verify
 * show a preview of the article if it is
 *    so that user can verify they copied the right URL
 */
import axios from "axios";
import { useEffect, useState } from "@wordpress/element";
import { Loader, Notification, Panel, ButtonGroup, Button } from "rsuite";

export default props => {
  const {url} = props;

  const [articleData, setArticleData] = useState(null);
  const [fetchError, setFetchError] = useState(false);
  const getArticleData = async () => {
    const data = {
      purpose: 'preview',
      url
    };
    const config = {
      'headers': {
        'X-WP-Nonce': window.wechat_import_page_data.nonce
      }
    };

    var resp;
    try {
      resp = await axios.post(wechat_import_page_data.rest_url, data, config);
    } catch(err) {
      setFetchError(true);
    }

    if (resp && resp.status === 200){
      setArticleData({
        title:        resp.data.title,
        description:  resp.data.description,
        thumbnail:    resp.data.thumbnail,
      });
      // setFetchError(false);
    }
  };
  useEffect(() => {
    getArticleData();
  });

  if (fetchError) {
    Notification['error']({
      title: 'Error',
      description: <>
          <p>Something went wrong!</p>
          <p>Try again!</p>
        </>,
      style: {marginTop: "2rem", fontWeight: "bold"}
    });
  }

  var content = <></>;
  if (articleData) {
    content = <PreviewCard {...articleData} />;
  } else if (!fetchError) {
    content = <Loader size="md" content="Loading Article Preview..." vertical />;
  }

  return <div className="step-2">
    <h3>这是你想要导入的文章吗?</h3>
    {content}
    <div className="steps-action">
      {
        articleData &&
        <ButtonGroup>
          <Button onClick={props.prev}>No, Abort</Button>
          <Button onClick={props.next}>Yes, Continue</Button>
        </ButtonGroup>
      }
      {
        fetchError &&
        <ButtonGroup>
          <Button onClick={props.prev}>Go Back to Edit</Button>
          <Button onClick={() => useEffect(() => getArticleData())}>Retry</Button>
        </ButtonGroup>
      }
    </div>
  </div>;
};

const PreviewCard = ({title, description, thumbnail}) => {
  return <Panel shaded bordered bodyFill>
    <img src={thumbnail} alt="thunmbnail image" height="240" style={{display: 'block', margin: "0 auto"}} />
    <Panel header={title}>
      <p>
        <small>{description}</small>
      </p>
    </Panel>
  </Panel>
}
