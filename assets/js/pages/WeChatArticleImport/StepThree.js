/**
 * The Processing Step
 * 1.
 */
import { Component } from "@wordpress/element";
import axios from "axios";
import { Progress, ButtonGroup, Button, Notification } from "rsuite";

const { Line } = Progress;
const processingSteps = [
  {
    status: 'active',
    description: 'Analyzing article for multimedia assets',
    percent: 17,
  },
  {
    status: 'active',
    description: 'Downloading multimedia assets in article',
    percent: 34,
  },
  {
    status: 'active',
    description: 'Processing/Replacing image urls in article',
    percent: 51,
  },
  {
    status: 'active',
    description: 'Processing thumbnail',
    percent: 68,
  },
  {
    status: 'active',
    description: 'Saving to database',
    percent: 85,
  },
  {
    status: 'success',
    description: 'Done',
    percent: 100,
  },
]

export default class StepThree extends Component {
  constructor(props) {
    super(props);
    this.state = {
      status: 'active',
      description: 'Sending article to server for analysis',
      percent: 0,
      editPostURL: '',
    };
    this.interval = null;
    this.articleSent = false;
  }

  sendArticleToProcess(){
    const data = {
      purpose: 'process',
      url: this.props.url
    };
    const config = {
      'headers': {
        'X-WP-Nonce': window.wechat_import_page_data.nonce
      }
    };
    axios.post(wechat_import_page_data.rest_url, data, config)
    .catch(e => {
      this.setState({status: 'fail'});
      this.errorNotification();
    });
    this.interval = setInterval(() => {
      this.fetchProgress();
    }, 1500);

    this.articleSent = true;
  }

  errorNotification() {
    Notification['error']({
      title: 'Error',
      description: <>
          <p>Something went wrong</p>
          <p>Try again later</p>
        </>,
      style: {marginTop: "2rem", fontWeight: "bold"}
    });
  }

  async fetchProgress(){
    const data = {
      purpose: 'status',
      url: this.props.url
    };
    const config = {
      'headers': {
        'X-WP-Nonce': window.wechat_import_page_data.nonce
      }
    };
    const errorHandler = () => {
      this.setState({status: 'fail'});
      clearInterval(this.interval);
      this.errorNotification();
    };
    try {
      const resp = await axios.post(wechat_import_page_data.rest_url, data, config);
      if (resp.status !== 200){
        errorHandler();
        return;
      }
      if (!resp.data)
        return;
      /**
       * resp.data:
       *  - {status: 'error'}
       *  - {status: 'processing', step: n}
       *  - {status: 'finished', postURL: url}
       */
      switch (resp.data.status) {
        case 'error':
          errorHandler();
          return;
        case 'processing':
          const processingStepInfo = processingSteps[resp.data.step-1];
          this.setState({...processingStepInfo});
          return;
        case 'finished':
          this.setState({
            ...processingSteps[processingSteps.length-1],
            editPostURL: resp.data.postURL
          });
          clearInterval(this.interval);
          return;
        default:
          break;
      }
    } catch (error) {
      errorHandler();
    }
  }

  componentDidMount() {
    if (this.props.url && !this.articleSent) {
      this.sendArticleToProcess();
    }
  }
  // componentDidUpdate() {
  //   console.log('compoent did update');
  //   if (this.props.url && !this.articleSent) {
  //     this.sendArticleToProcess();
  //   }
  // }

  render() {
    const {status, description, percent} = this.state;
    return <div className="step-3">
      <div className="progress-description">{description}</div>
      <Line percent={percent} status={status} showInfo={false} />
      <div className="steps-action">
        <ButtonGroup>
          <Button href={this.state.editPostURL} disabled={!this.state.editPostURL}>
            Continue Editing in Posts
          </Button>
        </ButtonGroup>
      </div>
    </div>;
  }
}
