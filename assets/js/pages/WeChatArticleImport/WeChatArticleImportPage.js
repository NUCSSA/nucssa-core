import { Component } from "@wordpress/element";
import { Steps, Button, ButtonGroup, Notification } from "rsuite";
import 'rsuite/dist/styles/rsuite-default.css';
import StepOne from './StepOne';
import StepTwo from "./StepTwo";


export default class WeChatArticleImportPage extends Component {
  constructor() {
    super();
    this.state = {
      step: 0,
      url: '',
      importSuccess: false,
      isURLValid: true,
      editPostLink: '',
    };
    this.next = this.next.bind(this);
    this.prev = this.prev.bind(this);
    this.setURL = this.setURL.bind(this);
  }

  next() {
    const step = this.state.step + 1;
    this.setState({ step });
  }

  prev() {
    const step = this.state.step - 1;
    this.setState({ step });
  }

  setURL(url) {
    this.setState({ url });
  }

  validateWeChatURL() {
    // limit this test to only Step One
    if (this.state.step !== 0) return true;

    if (!this.state.url.includes('mp.weixin.qq')){
      Notification['error']({
        title: 'Error',
        description: <>
            <p>这似乎不是微信公众号的文章</p>
            <p>请修正</p>
          </>,
        style: {marginTop: "2rem", fontWeight: "bold"}
      });

      return false;
    }
    return true;
  }

  render() {
    const { step, url } = this.state;
    const steps = [
      {
        title: 'URL',
        content: <StepOne url={url} setURL={this.setURL} />,
      },
      {
        title: 'Verify',
        content: <StepTwo url={url} />,
      },
      {
        title: 'Import',
        content: 'Last-content',
      },
      {
        title: 'Done',
        content: 'Last-content',
      },
    ];

    return (
      <>
        <Steps current={step}>
          {steps.map(item => (
            <Steps.Item key={item.title} title={item.title} />
          ))}
        </Steps>
        <div className="steps-content">{steps[step].content}</div>
        <div className="steps-action">
          <ButtonGroup>
            {
              step === 1 &&
              <Button onClick={this.prev}>Back</Button>
            }
            {
              (step !== (steps.length - 1) && url !== '') &&
              <Button onClick={() => this.validateWeChatURL() && this.next()}>Continue</Button>
            }
            {
              (step == steps.length - 1) && this.state.importSuccess &&
              <Button href={this.state.editPostLink}>Continue Editing in Posts</Button>
            }
          </ButtonGroup>
        </div>
      </>
    );
  }
}
