**So basically, we're going with smth Vue-ish....React-ish**
**We're going to have a leaf veins component which  we can extend to create our own components**

```php
use Leaf\Veins\Component;

class Counter extends Component {
    $this->state = [
		"count" => 0
    ];
    
    public function componentDidMount() {
        $this->setState([
            "count" => 1
        ]);
    }

    public function increment() {
        $count = $this->state->count;
        $this->setState([
            "count" => $count++
        ]);
    }

    public function decrement() {
        $count = $this->state->count;
        $this->setState([
            "count" => $count--
        ]);
    }

    public function render() {
		$this->set(["count", $this->state->count, "state" => $this->state]);
        $this->renderTemplate('views/counter');
    }
}
```