/**
 * Verify
 * 1. verify the url is wechat article
 * 2. then show a preview of the article if it is
 *    so that user can verify they copied the right URL
 */
import axios from "axios";
import { useEffect, useState } from "@wordpress/element";
import { Loader, Notification } from "rsuite";

export default props => {
  const {url} = props;

  const [articleData, setArticleData] = useState(null);
  const [fetchError, setFetchError] = useState(false);
  useEffect(() => {
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
      const content = await axios.post(wechat_import_page_data.rest_url,
                                       data, config);

      if (content.status === 200){
        setArticleData({
          title:        content.data.title,
          description:  content.data.description,
          thumbnail:    content.data.thumbnail,
        });
      } else {
        setFetchError(true);
      }
    };
    getArticleData();
  }, []);

  console.log('articleData', articleData);
  if (fetchError) {
    Notification['error']({
      title: 'Error',
      description: <>
          <p>Something has gone wrong!</p>
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
  content = <Loader size="md" speed="slow" content="Loading Article Preview..." vertical />;

  return <div className="step-2">
    <h3>这是你想要导入的文章吗?</h3>
    {content}
  </div>;
};

const PreviewCard = ({title, description, thumbnail}) => {
  return <></>
}
