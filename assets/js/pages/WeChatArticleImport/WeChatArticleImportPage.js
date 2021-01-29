import { Component } from "@wordpress/element";
import { Steps } from "rsuite";
import 'rsuite/dist/styles/rsuite-default.css';
import StepOne from './StepOne';
import StepTwo from "./StepTwo";
import StepThree from "./StepThree";


export default class WeChatArticleImportPage extends Component {
  constructor() {
    super();
    this.state = {
      step: 0,
      url: '',
      importSuccess: false,
      nextEnabled: true,
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



  render() {
    const { step, url } = this.state;
    const steps = [
      {
        title: 'URL',
        content: <StepOne url={url} setURL={this.setURL} next={this.next} />,
      },
      {
        title: 'Verify',
        content: <StepTwo url={url} prev={this.prev} next={this.next} />,
      },
      {
        title: 'Import',
        content: <StepThree url={url} />
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
      </>
    );
  }
}
